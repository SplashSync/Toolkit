<?php

namespace Nodes\FakerBundle\Tests\Controller;

use Nodes\CoreBundle\Entity\Node;
use Nodes\FakerBundle\Entity\FakeNode;

use Nodes\CoreBundle\Tests\Controller\BaseControllerTest;

class A001CoreControllerTest extends BaseControllerTest
{
    
    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        global $kernel;
        
        parent::setUp();
        //==============================================================================
        // Push Symfony Kernel as Global
        $kernel = static::$kernel;
        
        return True;
    }  
    
    /**
     * @abstract    Init Fake Client Node
     * 
     * @return \Nodes\FakerBundle\Entity\FakeNode
     */
    public function Init($Name)
    {
        //====================================================================//
        // Load Fake Node
        $FakeNode = $this->faker->getByName($Name);
        $this->assertInstanceOf(FakeNode::class , $FakeNode);
        //====================================================================//
        // Setup Fake Node as Current
        $this->faker->setCurrentFaker($FakeNode->getIdentifier());
        
        return $FakeNode;
    }      
    
    /**
     * @abstract    Test Add of a Fake Node
     * @dataProvider nodeNamesProvider
     */
    public function testAddFakeNode($Name)
    {
        //==============================================================================
        // Load Node Object
        $Node = $this->_em
                ->getRepository('NodesCoreBundle:Node')
                ->findOneByName($Name);
        
        //==============================================================================
        // In Case NodeCoreBundle Tests Not Already Done
        if (!$Node) {
            $this->testCreateNode($Name);
            $Node = $this->_em
                ->getRepository('NodesCoreBundle:Node')
                ->findOneByName($Name);
        }
        
        $this->assertInstanceOf(Node::class , $Node);

        //====================================================================//
        // Setup Fake Node
        $FakeNode = $this->faker
                ->setNode($Name, $Node);
        
        $this->assertInstanceOf(FakeNode::class , $FakeNode);
    }  
    
    /**
     * @abstract    Test Client Configuration
     * @dataProvider nodeNamesProvider
     */
    public function testClientConfiguration($Name)
    {
        //====================================================================//
        // Load Fake Node
        $FakeNode = $this->faker->getByName($Name);
        $this->assertInstanceOf(FakeNode::class , $FakeNode);
        //====================================================================//
        // Read Fake Node Parameters
        $Parameters =   $FakeNode->getWsParameters();
        //====================================================================//
        // Verify Parameters
        $this->assertNotEmpty($Parameters['WsIdentifier']);
        $this->assertNotEmpty($Parameters['WsEncryptionKey']);
        $this->assertNotEmpty($Parameters['ServerHost']);
        $this->assertNotEmpty($Parameters['ServerPath']);
    }         
    
    /**
     * @abstract    Test Client Ping Action
     * @dataProvider nodeNamesProvider
     */
    public function testClientPing($Name)
    {
        //====================================================================//
        // Load Fake Node
        $this->Init($Name);
        //====================================================================//
        // Execute Ping Tests
        $this->assertTrue($this->faker->doClientPing());
    }      
    
    /**
     * @abstract    Test Client Connect Action
     * @dataProvider nodeNamesProvider
     */
    public function testClientConnect($Name)
    {
        //====================================================================//
        // Load Fake Node
        $this->Init($Name);
        //====================================================================//
        // Execute Connect Tests
        $this->assertTrue($this->faker->doClientConnect());
    }      
    
    /**
     * @abstract    Test Client SelfTest Action
     * @dataProvider nodeNamesProvider
     */
    public function testClientSelfTest($Name)
    {
        $_SERVER["SPLASH_TRAVIS"] =  true;
        //====================================================================//
        // Load Fake Node
        $this->Init($Name);
        //====================================================================//
        // Execute SelfTest Tests
        $this->assertTrue($this->faker->doClientSelfTest());
    }      
    

    /**
     * @abstract    Test Client Objects List Action
     * @dataProvider nodeNamesProvider
     */
    public function testClientInformations($Name)
    {
        //====================================================================//
        // Load Fake Node
        $this->Init($Name);
        //====================================================================//
        // Read Client Informations
        $Data =  $this->faker->doClientInformations();
        //====================================================================//
        // Verify Response
        $this->assertGreaterThan( 0,  count($Data) );
        foreach ($Data as $Info) {
            if (is_null($Info)) {
                continue;
            }
            $this->assertInternalType( "string" , $Info);
        }
    }      
    
}
