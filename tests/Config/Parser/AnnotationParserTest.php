<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Tests\Config\Parser;

use Overblog\GraphQLBundle\Config\Parser\AnnotationParser;

class AnnotationParserTest extends TestCase
{
    protected function checkConfigFromFile($filename, $expected): void
    {
        $fileName = __DIR__.'/fixtures/Entity/GraphQL/'.$filename;
        $this->assertContainerAddFileToResources($fileName);
        $config = AnnotationParser::parse(new \SplFileInfo($fileName), $this->containerBuilder);
        $this->assertEquals($expected, self::cleanConfig($config));
    }

    public function testType(): void
    {
        $expected = [
            'Hero' => [
                'type' => 'object',
                'config' => [
                    'fields' => [
                        'name' => [
                            'type' => 'String!',
                            'deprecationReason' => 'it is now deprecated',
                        ],
                        'friends' => [
                            'type' => '[Character]',
                            'resolve' => "@=resolver('App\\\\MyResolver::getFriends')",
                        ],
                    ],
                    'description' => 'The Hero type',
                ],
            ],
        ];
        $this->checkConfigFromFile('Type/Hero.php', $expected);
    }

    public function testInput(): void
    {
        $expected = [
            'PlanetInput' => [
                'type' => 'input-object',
                'config' => [
                    'fields' => [
                        'name' => ['type' => 'String!'],
                        'population' => ['type' => 'Int!'],
                    ],
                    'description' => 'Planet Input type description',
                ],
            ],
        ];
        $this->checkConfigFromFile('Input/Planet.php', $expected);
    }

    public function testEnum(): void
    {
        $expected = [
            'PlanetEnum' => [
                'type' => 'enum',
                'config' => [
                    'values' => [
                        'DAGOBAH' => ['value' => 1],
                        'TATOUINE' => ['value' => '2', 'description' => 'The planet of Tatouine'],
                        'HOTH' => ['value' => '3'],
                        'BESPIN' => ['value' => '4'],
                    ],
                    'description' => 'The list of planets!',
                ],
            ],
        ];
        $this->checkConfigFromFile('Enum/Planet.php', $expected);
    }

    public function testUnion(): void
    {
        $expected = [
            'Pet' => [
                'type' => 'union',
                'config' => [
                    'types' => ['Dog', 'Cat', 'Bird', 'Snake'],
                    'description' => 'All the pets',
                ],
            ],
        ];
        $this->checkConfigFromFile('Union/Pet.php', $expected);
    }

    public function testScalar(): void
    {
        $expected = [
            'MyScalar' => [
                'type' => 'custom-scalar',
                'config' => [
                    'serialize' => ['Overblog\GraphQLBundle\Tests\Config\Parser\fixtures\Entity\GraphQL\Scalar\MyScalar', 'serialize'],
                    'parseValue' => ['Overblog\GraphQLBundle\Tests\Config\Parser\fixtures\Entity\GraphQL\Scalar\MyScalar', 'parseValue'],
                    'parseLiteral' => ['Overblog\GraphQLBundle\Tests\Config\Parser\fixtures\Entity\GraphQL\Scalar\MyScalar', 'parseLiteral'],
                    'description' => 'My custom scalar',
                ],
            ],
        ];
        $this->checkConfigFromFile('Scalar/MyScalar.php', $expected);
    }

    public function testScalar2(): void
    {
        $expected = [
            'MyScalar' => [
                'type' => 'custom-scalar',
                'config' => [
                    'scalarType' => "@=newObject('App\\\\Type\\\\EmailType')",
                ],
            ],
        ];

        $this->checkConfigFromFile('Scalar/MyScalar2.php', $expected);
    }

    public function testInterface(): void
    {
        $expected = [
            'Character' => [
                'type' => 'interface',
                'config' => [
                    'fields' => [
                        'id' => ['type' => 'String!', 'description' => 'The id of the character'],
                        'name' => ['type' => 'String!', 'description' => 'The name of the character'],
                    ],
                    'description' => 'The character interface',
                ],
            ],
        ];
        $this->checkConfigFromFile('Interfaces/Character.php', $expected);
    }

    public function testAccess(): void
    {
        $expected = [
            'HeroWithAccess' => [
                'type' => 'object',
                'config' => [
                    'fieldsDefaultAccess' => '@=isAuthenticated()',
                    'fields' => [
                        'name' => ['type' => 'String!'],
                        'secret' => [
                            'type' => 'Boolean!',
                            'access' => "@=hasRole('ROLE_ADMIN')",
                        ],
                    ],
                ],
            ],
        ];
        $this->checkConfigFromFile('Type/HeroWithAccess.php', $expected);
    }

    public function testPublic(): void
    {
        $expected = [
            'HeroWithPublic' => [
                'type' => 'object',
                'config' => [
                    'fieldsDefaultPublic' => '@=isAuthenticated()',
                    'fields' => [
                        'name' => ['type' => 'String!'],
                        'secret' => [
                            'type' => 'Boolean!',
                            'public' => "@=hasRole('ROLE_ADMIN')",
                        ],
                    ],
                ],
            ],
        ];
        $this->checkConfigFromFile('Type/HeroWithPublic.php', $expected);
    }

    public function testFieldMethod(): void
    {
        $expected = [
            'Type' => [
                'type' => 'object',
                'config' => [
                    'fields' => [
                        'friends' => [
                            'type' => '[Character]',
                            'args' => [
                                'gender' => ['type' => 'Gender', 'description' => 'Limit friends of this gender'],
                                'limit' => ['type' => 'Int', 'description' => 'Limit number of friends to retrieve'],
                            ],
                            'resolve' => "@=value.getFriends(args['gender'], args['limit'])",
                        ],
                    ],
                ],
            ],
        ];
        $this->checkConfigFromFile('Fields/FieldMethod.php', $expected);
    }

    public function testFieldArgsBuilder(): void
    {
        $expected = [
            'Type' => [
                'type' => 'object',
                'config' => [
                    'fields' => [
                        'friends' => [
                            'type' => '[Character]',
                            'argsBuilder' => [
                                'builder' => 'MyArgBuilder',
                                'config' => ['defaultArg' => 1, 'option2' => 'smile'],
                            ],
                            'resolve' => '@=value.getFriends()',
                        ],
                        'planets' => [
                            'argsBuilder' => 'MyArgBuilder',
                            'type' => 'String',
                        ],
                    ],
                ],
            ],
        ];
        $this->checkConfigFromFile('Fields/FieldArgsBuilder.php', $expected);
    }

    public function testFieldFieldBuilder(): void
    {
        $expected = [
            'Type' => [
                'type' => 'object',
                'config' => [
                    'fields' => [
                        'id' => ['builder' => 'GenericIdBuilder'],
                        'notes' => [
                            'builder' => 'NoteFieldBuilder',
                            'builderConfig' => ['option' => 'value'],
                        ],
                    ],
                ],
            ],
        ];
        $this->checkConfigFromFile('Fields/FieldFieldBuilder.php', $expected);
    }

    public function testExtends(): void
    {
        $expected = ['ChildClass' => ['type' => 'object', 'config' => [
            'fields' => [
                'id' => ['builder' => 'GenericIdBuilder'],
                'notes' => [
                    'builder' => 'NoteFieldBuilder',
                    'builderConfig' => ['option' => 'value'],
                ],
            ],
        ]]];
        $this->checkConfigFromFile('Inherits/ChildClass.php', $expected);
    }

    public function testTypeAutoGuessing(): void
    {
        $file1 = __DIR__.'/fixtures/Entity/GraphQL/Autoguess/Autoguess.php';
        $file2 = __DIR__.'/fixtures/Entity/GraphQL/Autoguess/Autoguess2.php';
        AnnotationParser::preParse(new \SplFileInfo($file1), $this->containerBuilder);
        AnnotationParser::preParse(new \SplFileInfo($file2), $this->containerBuilder);

        $config = AnnotationParser::parse(new \SplFileInfo($file1), $this->containerBuilder);

        $expected = ['Autoguess' => ['type' => 'object', 'config' => [
            'fields' => [
                'field1' => ['type' => 'String!'],
                'field2' => ['type' => 'Int'],
                'field3' => ['type' => '[CustomAutoguessType]!'],
                'field4' => ['type' => 'CustomAutoguessType!'],
                'field5' => ['type' => 'CustomAutoguessType!'],
                'field6' => ['type' => '[CustomAutoguessType]!'],
                'field7' => ['type' => 'CustomAutoguessType'],
            ],
        ]]];

        $this->assertEquals($expected, self::cleanConfig($config));
    }
}
