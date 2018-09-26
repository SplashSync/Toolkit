<?php

namespace Nodes\FakerBundle\Tests\Controller;

use Splash\Client\Splash;
use Nodes\CoreBundle\Entity\Node;
use Nodes\FakerBundle\Entity\FakeNode;
use Nodes\FakerBundle\Entity\FakeObject;

use Nodes\CoreBundle\Tests\Controller\BaseControllerTest;



class A002ObjectsControllerTest extends BaseControllerTest
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
     * @abstract    Test Add of Fake Objects Types To a Fake Node
     * @dataProvider nodeNamesProvider
     */
    public function testAddFakeObjectsTypes($Name,$ObjectTypeCfg)
    {
        //====================================================================//
        // Load Fake Node
        $FakeNode = $this->Init($Name);
        //====================================================================//
        // Setup Fake Node ObjectsTypes
        $this->assertInternalType( "array" , $ObjectTypeCfg, "Given Node Objects Types list is Empty");
        //====================================================================//
        // Generate Fake Node ObjectTypes
        $Types  = array();
        foreach ($ObjectTypeCfg as $ObjectType => $FieldSetType ) {
            $Types[]                =   $ObjectType;
        }
        $FakeNode->setObjectsTypes($Types);
        //====================================================================//
        // Generate Fake Node ObjectsFields
        $Fields = array();
        foreach ($ObjectTypeCfg as $ObjectType => $FieldSetType ) {
            $Fields[$ObjectType]    =   $this->faker->generateFieldsSet($FieldSetType);
        }
        $FakeNode->setObjectsFields($Fields);
        //====================================================================//
        // Save
        $this->_em->flush();
    }      
    
    /**
     * @abstract    Test Client Objects List Action
     * @dataProvider nodeNamesProvider
     */
    public function testClientObjects($Name)
    {
        //====================================================================//
        // Load Fake Node
        $FakeNode = $this->Init($Name);
        //====================================================================//
        // Read Client Object List
        $ObjectList =  $this->faker->doClientObjects();
        //====================================================================//
        // Verify Response
        $this->assertInternalType( "array" , $ObjectList);
        foreach ($ObjectList as $ObjectName) {
            $this->assertInternalType( "string" , $ObjectName);
        }
        //====================================================================//
        // Verify Vs Faker Config
        foreach ($FakeNode->getObjectsTypes() as $ObjectName) {
            $this->assertContains( $ObjectName , $ObjectList, "Object List are not similar!");
        }
        $this->assertEquals( count($FakeNode->getObjectsTypes()) , count($ObjectList), "Object List are not similar!");
    }      

    /**
     * @abstract    Test Client Objects List Action
     * @dataProvider nodeNamesProvider
     */
    public function testClientObjectsFields($Name)
    {
        //====================================================================//
        // Load Fake Node
        $FakeNode = $this->Init($Name);
        //====================================================================//
        // Verify Right FakeNode is Loaded
        $this->assertEquals( $FakeNode->getName() , Splash::local()->getFakeNode()->getName(), "Compare Requested & Loaded FakeNode Names");                
        //====================================================================//
        // Run through All Fake Node ObjectsTypes
        foreach ($FakeNode->getObjectsTypes() as $ObjectType) {
            //====================================================================//
            // Read Client Object List
            $FieldsList =  $this->faker->doClientObjectFields($ObjectType);
            foreach ($FieldsList as $Index => $FieldDefinition) {
                //====================================================================//
                // Verify Response
                $this->assertEquals( $FakeNode->getObjectFields($ObjectType)[$Index] , $FieldDefinition, "Compare Field for " . $FakeNode->getName() . " Object Type " . $ObjectType . " Item " . $Index);                
            }
            //====================================================================//
            // Verify Response
            $this->assertEquals( $FakeNode->getObjectFields($ObjectType) , $FieldsList, "Compare Field for " . $FakeNode->getName() . " Object Type " . $ObjectType);
        }
    }    
    
    /**
     * @abstract    Test Client Object Get Action
     * @dataProvider nodeNamesProvider
     */
    public function testClientObjectGetAll($Name)
    {
        //====================================================================//
        // Load Fake Node
        $FakeNode = $this->Init($Name);
        //====================================================================//
        // Run through All Fake Node ObjectsTypes
        foreach ($FakeNode->getObjectsTypes() as $ObjectType) {
            //====================================================================//
            // Manually Add an Object To Fake Node
            //====================================================================//
            if ( $FakeNode->getObjects($ObjectType)->count() > 0 ) {
//                continue;
            }
            //====================================================================//
            // Manually Add an Object To Fake Node
            $FieldsList     =   $FakeNode->getObjectFields($ObjectType);
            $FakeObject     =   $FakeNode->createObject($ObjectType);
            //====================================================================//
            // Setup Object Formater
            $Formater       = $this->faker->getFormater();
            $Formater->setSetting("Objects",$FakeNode->getObjectsFormaterList(Null, 10));
            //====================================================================//
            // Setup Fake Object 
            $FakeObject->setData($Formater->fakeObjectData($FieldsList));
            //====================================================================//
            // Save
            $this->_em->persist($FakeObject);
            $this->_em->flush();
        }
        
        //====================================================================//
        // Run through All Fake Node ObjectsTypes
        foreach ($FakeNode->getObjectsTypes() as $ObjectType) {
            //====================================================================//
            // Load First Object 
            $FirstObject = $FakeNode->getObjects($ObjectType)->first();
            $this->assertInstanceOf(FakeObject::class , $FirstObject);
            //====================================================================//
            // Do Client Object Get Data Action for All Fields
            $ObjectData =  $this->faker->doClientObjectGet($FirstObject->getType(), $FirstObject->getIdentifier());
            //====================================================================//
            // Verify Response
            $this->assertArrayHasKey( "id" , $ObjectData);
            $this->assertNotEmpty( $ObjectData["id"] );
            unset($ObjectData["id"]);
            $this->assertEquals( $FirstObject->getData() , $ObjectData);
        }
        
    }  
    
    /**
     * @abstract    Test Client Object Get Action
     * @dataProvider nodeNamesProvider
     */
    public function testClientObjectGetOne($Name)
    {
        //====================================================================//
        // Load Fake Node
        $FakeNode = $this->Init($Name);
        //====================================================================//
        // Run through All Fake Node Objects Types
        foreach ($FakeNode->getObjectsTypes() as $ObjectType) {
            //====================================================================//
            // Load First Object 
            $FirstObject = $FakeNode->getObjects($ObjectType)->first();
            $this->assertInstanceOf(FakeObject::class , $FirstObject);

            //====================================================================//
            // Run through All Fake Node Objects Fields
            $FieldsList = $FakeNode->getObjectFields($ObjectType);
            foreach ($FieldsList as $Field) {
                //====================================================================//
                // Build Single Field List
                $SingleFieldList    = $this->faker->getFormater()
                        ->filterFieldList($FieldsList, array($Field->id));
                //====================================================================//
                // Build Single Field Data Block
                $SingleFieldData    =   $this->faker->getFormater()
                        ->filterData($FirstObject->getData(), array($Field->id));
                //====================================================================//
                // Do Client Object Get Data Action for One Field
                $GetResponse =  $this->faker->doClientObjectGet($FirstObject->getType(), $FirstObject->getIdentifier(), $SingleFieldList);
                //====================================================================//
                // Verify Response
                $this->assertArrayHasKey( "id" , $GetResponse);
                $this->assertNotEmpty( $GetResponse["id"] );
                unset($GetResponse["id"]);                
                $this->assertEquals( $SingleFieldData , $GetResponse);
            }
        }
    }  
    
    /**
     * @abstract    Test Client Object Set Action
     * @dataProvider nodeNamesProvider
     */
    public function testClientObjectSetAll($Name)
    {
        //====================================================================//
        // Load Fake Node
        $FakeNode = $this->Init($Name);
        //====================================================================//
        // Run through All Fake Node ObjectsTypes
        foreach ($FakeNode->getObjectsTypes() as $ObjectType) {
            //====================================================================//
            // Load First Object 
            $FirstObject = $FakeNode->getObjects($ObjectType)->first();
            $this->assertInstanceOf(FakeObject::class , $FirstObject);
            //====================================================================//
            // Generate New Data for this Object
            $FieldsList     =   $FakeNode->getObjectFields($ObjectType);
            //====================================================================//
            // Setup Object Formater
            $Formater       = $this->faker->getFormater();
            $Formater->setSetting("Objects",$FakeNode->getObjectsFormaterList(Null, 10));
            $NewData        =   $Formater->fakeObjectData($FieldsList);
            //====================================================================//
            // Do Client Object Set Data Action for All Fields
            $SetResponse =  $this->faker->doClientObjectSet($FirstObject->getType(), $FirstObject->getIdentifier(),$NewData);
            //====================================================================//
            // Verify Response
            $this->assertEquals( $SetResponse, $FirstObject->getIdentifier() );
            //====================================================================//
            // Do Client Object Get Data Action for All Fields
            $GetResponse =  $this->faker->doClientObjectGet($FirstObject->getType(), $FirstObject->getIdentifier());
            //====================================================================//
            // Verify Response
            unset($GetResponse["id"]); 
            $this->assertEquals( $GetResponse , $NewData );
        }
        
    }      
    
    /**
     * @abstract    Test Client Object Set Action
     * @dataProvider nodeNamesProvider
     */
    public function testClientObjectSetOne($Name)
    {
        //====================================================================//
        // Load Fake Node
        $FakeNode = $this->Init($Name);
        //====================================================================//
        // Run through All Fake Node ObjectsTypes
        foreach ($FakeNode->getObjectsTypes() as $ObjectType) {
            //====================================================================//
            // Load First Object 
            $FirstObject = $FakeNode->getObjects($ObjectType)->first();
            $this->assertInstanceOf(FakeObject::class , $FirstObject);
            //====================================================================//
            // Generate New Data for this Object
            $FieldsList     =   $FakeNode->getObjectFields($ObjectType);
            //====================================================================//
            // Setup Object Formater
            $Formater       = $this->faker->getFormater();
            $Formater->setSetting("Objects",$FakeNode->getObjectsFormaterList(Null, 10));
            $NewData        =   $Formater->fakeObjectData($FieldsList);
            //====================================================================//
            // Run through All Fake Node Objects Fields
            foreach ($FakeNode->getObjectFields($ObjectType) as $Field) {
                //====================================================================//
                // Build Single Field List
                $SingleFieldList    = $this->faker->getFormater()
                        ->filterFieldList($FieldsList, array($Field->id));
                //====================================================================//
                // Build Single Field Data Block
                $SingleFieldData    =   $this->faker->getFormater()
                        ->filterData($NewData, array($Field->id));
                //====================================================================//
                // Do Client Object Set Data Action for All Fields
                $SetResponse =  $this->faker->doClientObjectSet($FirstObject->getType(), $FirstObject->getIdentifier(),$SingleFieldData);
                //====================================================================//
                // Verify Response
                $this->assertEquals( $SetResponse, $FirstObject->getIdentifier() );
                //====================================================================//
                // Do Client Object Set Data Action for All Fields
                $GetResponse =  $this->faker->doClientObjectGet($FirstObject->getType(), $FirstObject->getIdentifier(),$SingleFieldList);
                //====================================================================//
                // Verify Response
                unset($GetResponse["id"]); 
                $this->assertEquals( $GetResponse , $SingleFieldData );
            }
        }
        
    }       
    
    /**
     * @abstract    Test Client Object Delete Action
     * @dataProvider nodeNamesProvider
     */
    public function testClientObjectDelete($Name)
    {
        //====================================================================//
        // Load Fake Node
        $FakeNode = $this->Init($Name);
        //====================================================================//
        // Run through All Fake Node ObjectsTypes
        foreach ($FakeNode->getObjectsTypes() as $ObjectType) {
            //====================================================================//
            // Load First Object 
            $FirstObject = $FakeNode->getObjects($ObjectType)->first();
            $this->assertInstanceOf(FakeObject::class , $FirstObject);
            //====================================================================//
            // Do Client Object Delete Action for All Fields
            $Response =  $this->faker->doClientObjectDelete($FirstObject->getType(), $FirstObject->getIdentifier());
            //====================================================================//
            // Verify Response
            $this->assertEquals( $Response, True );
            //====================================================================//
            // Try to Load this Object Again
            $DeletedObject = $FakeNode->getObject($ObjectType, $FirstObject->getIdentifier());
            //====================================================================//
            // Verify Response
            $this->assertEquals( False , $DeletedObject , "Try to Read Deleted Object MUST return False");
        }
        
    } 
    
}
