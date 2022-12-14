<?php

namespace MilexPlugin\MilexCloudStorageBundle\Integration;

use Aws\S3\S3Client;
use Gaufrette\Adapter\AwsS3;
use Gaufrette\Extras\Resolvable\ResolvableFilesystem;
use Gaufrette\Extras\Resolvable\Resolver\AwsS3PublicUrlResolver;
use Gaufrette\Filesystem;
use MilexPlugin\MilexCloudStorageBundle\Exception\NoFormNeededException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;

class AmazonS3Integration extends CloudStorageIntegration
{
    /**
     * @var ResolvableFilesystem
     */
    private $fileSystem;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'AmazonS3';
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return 'Amazon S3';
    }

    /**
     * Get the array key for clientId.
     *
     * @return string
     */
    public function getClientIdKey()
    {
        return 'client_id';
    }

    /**
     * Get the array key for client secret.
     *
     * @return string
     */
    public function getClientSecretKey()
    {
        return 'client_secret';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredKeyFields()
    {
        return [
            'client_id'     => 'milex.integration.keyfield.clientid',
            'client_secret' => 'milex.integration.keyfield.clientsecret',
            'bucket'        => 'milex.integration.keyfield.amazons3.bucket',
        ];
    }

    /**
     * @param Form|FormBuilder $builder
     * @param array            $data
     * @param string           $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
        if ('keys' === $formArea) {
            $builder->add(
                'region',
                TextType::class,
                [
                    'label'    => 'milex.integration.Amazon.region',
                    'attr'     => ['class'   => 'form-control'],
                    'data'     => empty($data['region']) ? 'us-east-1' : $data['region'],
                    'required' => false,
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return AwsS3
     */
    public function getAdapter()
    {
        if (!$this->adapter || !$this->fileSystem) {
            $keys = $this->getDecryptedApiKeys();

            $service = new S3Client(
                [
                    'version'     => 'latest',
                    'region'      => (empty($keys['region'])) ? 'us-east-1' : $keys['region'],
                    'credentials' => [
                        'key'    => $keys['client_id'],
                        'secret' => $keys['client_secret'],
                    ],
                ]
            );

            $this->adapter    = new AwsS3($service, $keys['bucket']);
            $decorated        = new Filesystem($this->adapter);
            $this->fileSystem = new ResolvableFilesystem(
                $decorated,
                new AwsS3PublicUrlResolver($service, $keys['bucket'])
            );
        }

        return $this->adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        throw new NoFormNeededException();
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicUrl($key)
    {
        $this->getAdapter();

        return $this->fileSystem->resolve($key);
    }
}
