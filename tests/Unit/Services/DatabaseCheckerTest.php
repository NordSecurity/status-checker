<?php

declare(strict_types=1);

namespace Unit\Services;

use Nordsec\StatusChecker\Services\DatabaseChecker;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nordsec\StatusChecker\Services\DatabaseChecker
 */
class DatabaseCheckerTest extends TestCase
{
    /**
     * @dataProvider getTestCheckStatusCases
     */
    public function testCheckStatus(
        array $arguments,
        array $expectedPdoArgs,
        string $expectedResult,
        array $env = []
    ): void {
        if (!empty($env)) {
            foreach ($env as $envItem) {
                putenv($envItem);
            }
        }
        $databaseCheckerMock = $this->getMockBuilder(DatabaseChecker::class)
            ->setConstructorArgs($arguments)
            ->onlyMethods(['createConnection'])
            ->getMock()
        ;

        $databaseCheckerMock
            ->expects(static::once())
            ->method('createConnection')
            ->with(...$expectedPdoArgs)
            ->willReturn($this->createMock(\PDO::class))
        ;

        $actualResult = $databaseCheckerMock->checkStatus();

        static::assertSame($expectedResult, $actualResult);
    }

    public function getTestCheckStatusCases(): array
    {
        $cases = [];

        $cases['doctrine_mysql_url'] = [
            'arguments' => [
                'database.mysql_doctrine',
                [
                    'url' => 'mysql://user:pass@localhost:3306/testdb',
                ],
            ],
            'expectedPdoArgs' => [
                'mysql:host=localhost;port=3306;dbname=testdb',
                'user',
                'pass',
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION],
            ],
            'expectedResult' => DatabaseChecker::STATUS_OK,
        ];

        $cases['eloquent_mysql_url'] = [
            'arguments' => [
                'database.mysql_eloquent',
                [
                    'my_connection' => [
                        'url' => 'mysql://user2:pass2@127.0.0.1:3306/testdb2',
                    ],
                ],
            ],
            'expectedPdoArgs' => [
                'mysql:host=127.0.0.1;port=3306;dbname=testdb2',
                'user2',
                'pass2',
            ],
            'expectedResult' => DatabaseChecker::STATUS_OK,
        ];

        $cases['custom_mysql_url'] = [
            'arguments' => [
                'database.mysql_custom',
                'mysql://user3:pass3@127.0.0.3:3306/testdb3',
            ],
            'expectedPdoArgs' => [
                'mysql:host=127.0.0.3;port=3306;dbname=testdb3',
                'user3',
                'pass3',
            ],
            'expectedResult' => DatabaseChecker::STATUS_OK,
        ];

        $cases['doctrine_mysql_parameters'] = [
            'arguments' => [
                'database.mysql_doctrine',
                [
                    'dbname' => 'database',
                    'host' => 'localhost',
                    'port' => '1234',
                    'user' => 'user',
                    'password' => 'secret',
                    'driver' => 'pdo_mysql',
                ],
            ],
            'expectedPdoArgs' => [
                'mysql:host=localhost;port=1234;dbname=database',
                'user',
                'secret',
            ],
            'expectedResult' => DatabaseChecker::STATUS_OK,
        ];

        $cases['eloquent_mysql_parameters'] = [
            'arguments' => [
                'database.mysql_eloquent',
                [
                    'mysql' => [
                        'read' => [
                            'host' => '192.168.1.1',
                        ],
                        'write' => [
                            'host' => '196.168.1.2',
                        ],
                        'driver' => 'mysql',
                        'database' => 'database',
                        'username' => 'root',
                        'password' => '',
                        'charset' => 'utf8',
                        'collation' => 'utf8_unicode_ci',
                        'prefix' => '',
                    ],
                ],
            ],
            'expectedPdoArgs' => [
                'mysql:host=192.168.1.1;port=3306;dbname=database',
                'root',
                '',
            ],
            'expectedResult' => DatabaseChecker::STATUS_OK,
        ];

        $cases['propel_mysql_parameters'] = [
            'arguments' => [
                'database.mysql_propel',
                [
                    'bookstore' => [
                        'adapter' => 'mysql',
                        'classname' => 'Propel\Runtime\Connection\ConnectionWrapper',
                        'dsn' => 'mysql:host=localhost;dbname=my_db_name',
                        'user' => 'my_db_user',
                        'password' => 's3cr3t',
                        'attributes' => [],
                    ],
                ],
            ],
            'expectedPdoArgs' => [
                'mysql:host=localhost;dbname=my_db_name',
                'my_db_user',
                's3cr3t',
            ],
            'expectedResult' => DatabaseChecker::STATUS_OK,
        ];

        $cases['doctrine_sqlite_url_current_dir'] = [
            'arguments' => [
                'database.sqlite_doctrine',
                [
                    'url' => 'sqlite://testfile.db',
                ],
            ],
            'expectedPdoArgs' => [
                'sqlite:testfile.db',
            ],
            'expectedResult' => DatabaseChecker::STATUS_OK,
        ];

        $cases['eloquent_sqlite_url_current_dir'] = [
            'arguments' => [
                'database.sqlite_eloquent',
                [
                    'sqlite' => [
                        'url' => 'sqlite://testfile.db',
                    ],
                ],
            ],
            'expectedPdoArgs' => [
                'sqlite:testfile.db',
            ],
            'expectedResult' => DatabaseChecker::STATUS_OK,
        ];

        $cases['doctrine_sqlite_url_realpath'] = [
            'arguments' => [
                'database.sqlite_doctrine',
                [
                    'url' => 'sqlite:///var/testfile.db',
                ],
            ],
            'expectedPdoArgs' => [
                'sqlite:/var/testfile.db',
            ],
            'expectedResult' => DatabaseChecker::STATUS_OK,
        ];

        $cases['eloquent_sqlite_url_realpath'] = [
            'arguments' => [
                'database.sqlite_eloquent',
                [
                    'sqlite' => [
                        'url' => 'sqlite:///var/testfile.db',
                    ],
                ],
            ],
            'expectedPdoArgs' => [
                'sqlite:/var/testfile.db',
            ],
            'expectedResult' => DatabaseChecker::STATUS_OK,
        ];

        $cases['custom_sqlite_url'] = [
            'arguments' => [
                'database.sqlite_custom',
                'sqlite:///var/testfile.db',
            ],
            'expectedPdoArgs' => [
                'sqlite:/var/testfile.db',
            ],
            'expectedResult' => DatabaseChecker::STATUS_OK,
        ];

        $cases['custom_sqlite_dsn'] = [
            'arguments' => [
                'database.sqlite_custom',
                'sqlite:/var/testfile.db',
            ],
            'expectedPdoArgs' => [
                'sqlite:/var/testfile.db',
            ],
            'expectedResult' => DatabaseChecker::STATUS_OK,
        ];

        $cases['symfony_style_database_url'] = [
            'arguments' => [
                'database.mysql_symfony',
                [],
            ],
            'expectedPdoArgs' => [
                'mysql:host=localhost;port=3306;dbname=test_sf_db',
                'user1',
                'pass1',
            ],
            'expectedResult' => DatabaseChecker::STATUS_OK,
            'env' => [
                'DATABASE_URL=mysql://user1:pass1@localhost/test_sf_db',
            ],
        ];

        $cases['defaults_only'] = [
            'arguments' => [
                'database.mysql_defaults',
                [],
            ],
            'expectedPdoArgs' => [
                'mysql:host=localhost;port=3306;dbname=',
                'root',
                '',
            ],
            'expectedResult' => DatabaseChecker::STATUS_OK,
            'env' => [
                'DATABASE_URL=',
            ],
        ];

        return $cases;
    }
}
