<?php

declare(strict_types=1);

namespace Milex\CoreBundle\EventListener;

use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Milex\CoreBundle\Doctrine\Provider\GeneratedColumnsProviderInterface;
use Milex\CoreBundle\Doctrine\Type\GeneratedType;
use Psr\Log\LoggerInterface;

class DoctrineGeneratedColumnsListener
{
    /**
     * @var GeneratedColumnsProviderInterface
     */
    protected $generatedColumnsProvider;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(GeneratedColumnsProviderInterface $generatedColumnsProvider, LoggerInterface $logger)
    {
        $this->generatedColumnsProvider = $generatedColumnsProvider;
        $this->logger                   = $logger;
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schema           = $args->getSchema();
        $generatedColumns = $this->generatedColumnsProvider->getGeneratedColumns();

        foreach ($generatedColumns as $generatedColumn) {
            try {
                if (!$schema->hasTable($generatedColumn->getTableName())) {
                    continue;
                }

                $table = $schema->getTable($generatedColumn->getTableName());

                if ($table->hasColumn($generatedColumn->getColumnName())) {
                    continue;
                }

                $table->addColumn(
                    $generatedColumn->getColumnName(),
                    GeneratedType::GENERATED,
                    [
                        'columnDefinition' => $generatedColumn->getColumnDefinition(),
                        'notNull'          => false,
                    ]
                );

                $table->addIndex($generatedColumn->getIndexColumns(), $generatedColumn->getIndexName());
            } catch (\Exception $e) {
                //table doesn't exist or something bad happened so oh well
                $this->logger->error('SCHEMA ERROR: '.$e->getMessage());
            }
        }
    }
}
