<?php

use Page\Acceptance\Dashboard as DashboardPage;
use Page\Acceptance\Login as LoginPage;

$I = new AcceptanceTester($scenario);
$I->wantTo('Login to Milex');
$I->amOnPage(LoginPage::$URL);
$I->fillField(LoginPage::$username, 'admin');
$I->fillField(LoginPage::$password, 'milex');
$I->click(LoginPage::$login);
$I->seeCurrentUrlEquals(DashboardPage::$URL);
