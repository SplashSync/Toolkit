<?php

namespace Nodes\FakerBundle\Tests\Controller;

use Nodes\FakerBundle\Entity\FakeNode;

use Nodes\CoreBundle\Tests\Controller\BaseControllerTest;

class A003FilesControllerTest extends BaseControllerTest
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
     * @abstract    Test Faker isFile Function
     * @dataProvider nodeNamesProvider
     */
    public function testisFileFunction($Name)
    {
        //====================================================================//
        // Load Fake Node
        $this->Init($Name);        
        
        //====================================================================//
        // Link to OpenObjects Formater Services
        $Formater = static::$kernel->getContainer()->get('OpenObject.Formater.Service');        
        
        //====================================================================//
        // Generate a Dummy Images Field Array
        $FileField =   $Formater->fakeFieldData(SPL_T_FILE); 
        $this->assertNotEmpty($FileField);
        $this->assertNotEmpty($FileField["path"]);
        
        //====================================================================//
        // Client File isFile Function  => Normal Mode
        $Response =  $this->faker->doClientIsFileAction($FileField["path"], $FileField["md5"]);
        //====================================================================//
        // Verify Response
        $this->assertNotEmpty($Response);        
        $this->assertArrayHasKey("md5",$Response);        
        $this->assertArrayHasKey("size",$Response);        

        //====================================================================//
        // Client File isFile Function  => Wrong Parameters
        $this->assertFalse($this->faker->expectErrors()->doClientIsFileAction(Null, $FileField["md5"]));
        $this->assertFalse($this->faker->expectErrors()->doClientIsFileAction($FileField["path"], Null));
        //====================================================================//
        // Client File isFile Function  => Wrong Path
        $this->assertTrue($this->faker->doClientIsFileAction($FileField["path"] . "2", $FileField["md5"]));
        //====================================================================//
        // Client File isFile Function  => Wrong Md5
        $this->assertTrue($this->faker->doClientIsFileAction($FileField["path"], $FileField["md5"] . "2"));
    }   
    
    /**
     * @abstract    Test Faker ReadFile Function
     * @dataProvider nodeNamesProvider
     */
    public function testReadFileFunction($Name)
    {
        //====================================================================//
        // Load Fake Node
        $FakeNode = $this->Init($Name);        
        
        //====================================================================//
        // Link to OpenObjects Formater Services
        $Formater = static::$kernel->getContainer()->get('OpenObject.Formater.Service');        
        
        //====================================================================//
        // Generate a Dummy File Field Array
        $FileField =   $Formater->fakeFieldData(SPL_T_FILE); 
        $this->assertNotEmpty($FileField);
        $this->assertNotEmpty($FileField["path"]);
        
        //====================================================================//
        // Client File ReadFile Function  => Normal Mode
        $Response =  $this->faker->doClientReadFileAction($FileField["path"], $FileField["md5"]);
        //====================================================================//
        // Verify Response
        $this->assertNotEmpty($Response);        
        $this->assertArrayHasKey("md5",$Response);        
        $this->assertArrayHasKey("size",$Response);        
        $this->assertArrayHasKey("raw",$Response);        

        //====================================================================//
        // Client File ReadFile Function  => Wrong Parameters
        $this->assertFalse($this->faker->expectErrors()->doClientReadFileAction(Null, $FileField["md5"]));
        $this->assertFalse($this->faker->expectErrors()->doClientReadFileAction($FileField["path"], Null));
        //====================================================================//
        // Client File ReadFile Function  => Wrong Path
        $this->assertTrue($this->faker->doClientReadFileAction($FileField["path"] . "2", $FileField["md5"]));
        //====================================================================//
        // Client File ReadFile Function  => Wrong Md5
        $this->assertTrue($this->faker->doClientReadFileAction($FileField["path"], $FileField["md5"] . "2"));
    }       
    
    /**
     * @abstract    Test Faker WriteFile Function
     * @dataProvider nodeNamesProvider
     */
    public function testWriteFileFunction($Name)
    {
        //====================================================================//
        // Load Fake Node
        $FakeNode = $this->Init($Name);        
        
        //====================================================================//
        // Link to OpenObjects Formater Services
        $Formater = static::$kernel->getContainer()->get('OpenObject.Formater.Service');        
        
        //====================================================================//
        // Generate a Dummy File Field Array
        $FileField =   $Formater->fakeFieldData(SPL_T_FILE); 
        $this->assertNotEmpty($FileField);
        $this->assertNotEmpty($FileField["path"]);
        $this->assertNotEmpty($FileField["filename"]);
        
        //====================================================================//
        // Client File ReadFile Function  => Normal Mode
        $FileArray =  $this->faker->doClientReadFileAction($FileField["path"], $FileField["md5"]);
        //====================================================================//
        // Verify Response
        $this->assertNotEmpty($FileArray);        
        $this->assertArrayHasKey("md5",$FileArray);        
        $this->assertArrayHasKey("size",$FileArray);        
        $this->assertArrayHasKey("raw",$FileArray);        

        
        //====================================================================//
        // Prepare Full Path for Writing New File
        $Dir        = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . "/web/uploads/faker/" . $FakeNode->getIdentifier() . "/" ;  
        $WritePath = $Dir . $FileArray["filename"];
        
        if ( !is_file($WritePath) ) {
            //====================================================================//
            // Client File WriteFile Function  => Normal Mode
            $this->assertTrue($this->faker->doClientWriteFileAction($WritePath, $FileArray["md5"], $FileArray["raw"]));
            $this->assertEquals($FileArray["md5"],md5_file($WritePath));
        }
        
        //====================================================================//
        // Client File DeleteFile Function  => Wrong Path
        $this->assertTrue($this->faker->doClientDeleteFileAction($WritePath . "2", $FileArray["md5"]));
        $this->assertTrue(is_file($WritePath));
        
        //====================================================================//
        // Client File DeleteFile Function  => Wrong Md5
        $this->assertTrue($this->faker->doClientDeleteFileAction($WritePath, $FileArray["md5"] . "2"));
        $this->assertTrue(is_file($WritePath));
        
        //====================================================================//
        // Client File DeleteFile Function  => Normal Mode
        $this->assertTrue($this->faker->doClientDeleteFileAction($WritePath, $FileArray["md5"]));
        $this->assertFalse(is_file($WritePath));
        
        //====================================================================//
        // Client File WriteFile Function  => Normal Mode
        $this->assertTrue($this->faker->doClientWriteFileAction($WritePath, $FileArray["md5"], $FileArray["raw"]));
        $this->assertEquals($FileArray["md5"],md5_file($WritePath));
        
        //====================================================================//
        // Client File DeleteFile Function  => Normal Mode
        $this->assertTrue($this->faker->doClientDeleteFileAction($WritePath, $FileArray["md5"]));
        $this->assertFalse(is_file($WritePath));        
        
    }      
    
}
