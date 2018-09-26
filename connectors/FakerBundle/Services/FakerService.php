<?php

namespace Nodes\FakerBundle\Services;


use Splash\Client\Splash;
use Splash\Local\Local;

use Doctrine\ORM\EntityManager;

use UserBundle\Services\DocumentManagers;

use Nodes\CoreBundle\Entity\Node;
use Nodes\FakerBundle\Entity\FakeNode;
use Nodes\FakerBundle\Entity\FakeObject;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use OpenObject\CoreBundle\Document\OpenObjectFieldCore  as Field;
use OpenObject\WsSchemasBundle\Entity\WsSchema;
use OpenObject\CoreBundle\Services\Storage\FormaterService;

use OpenObject\CoreBundle\Event\ActionContextEvent;

use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * @abstract    Nodes Faker / Test Service
 */
class FakerService 
{
    const   FAKE_MODULE_PATH        =   "/vendor/";
    const   FAKE_MODULE_FILE        =   "/autoload.php";
    const   FAKE_FIELDFACTORY_FILE  =   "/class/SplashFieldsFactory.php";

    private $ObjectLinksFilter      =   array("Objects", "List", "File");
    
    /*
     *  Doctrine Entity Manager
     * @var \Doctrine\ORM\EntityManager
     */
    private $_em;
        
    /*
     *  Doctrine Document Manager
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $_dm;
    
    /*
     *  Symfony Service Container
     */
    private $_container;
    
    /*
     *  OpenObject Formater Service
     *  
     *  @var    FormaterService
     */
    protected $formater; 
    
    /*
     *  Current Request Identifier
     */
    private $_current;
    
    /*
     *  We Expect an Erro on next request
     */
    private $ExpectErrors = False;
    
//====================================================================//
//  CONSTRUCTOR
//====================================================================//
    
    /**
     *      @abstract    Class Constructor
     */    
    public function __construct(EntityManager $entityManager, DocumentManagers $documentManager, $container, FormaterService $FormaterService) { 
        //====================================================================//
        // Link to entity manager Service
        $this->_em          =   $entityManager;        
        //====================================================================//
        // Link to document manager Service
        $this->_dm          =   $documentManager;   
        //====================================================================//
        // Link to OpenObjects Formater Service
        $this->formater     =   $FormaterService;        
        //====================================================================//
        // Link to Service Container
        $this->_container      = $container;              
        //====================================================================//
        // Setup Splash Local Server
        Splash::core()->setLocalClass(new Local());
        return True;
    } 
    
//====================================================================//
//  SERVER INIT
//====================================================================//

    /**
     * @abstract    Create a Fake Node Entity
     * 
     * @param string    $WsIndentifer           
     * 
     * @return \Nodes\FakerBundle\Entity\FakeNode     
     */   
    public function setCurrentFaker($WsIndentifer)
    {
        $this->_current = $WsIndentifer;
        //====================================================================//
        // Activate Debug Mode
        if (!defined('SPLASH_DEBUG')) {
            define("SPLASH_DEBUG"           ,   True);         
        }
        return $this;
    }     
    
    /**
     * @abstract    Return OpenObject Formater
     * 
     * @return FormaterService
     */   
    public function getFormater()
    {
        return $this->formater;
    }   
    
    /**
     * @abstract    Tell Faker we expect next request to Fail
     * 
     * @return $this
     */   
    public function expectErrors()
    {
        $this->ExpectErrors = True;
        return $this;
    }  

    /**
     * @abstract    Check if Faker expect next request to Fail
     * 
     * @return bool
     */   
    public function isExpectErrors() : bool
    {
        if ( $this->ExpectErrors ) {
            $this->ExpectErrors = False;
            return True;
        }
        return False;
    }  
    
//====================================================================//
//  SERVER OPERATIONS
//====================================================================//

    /**
     * @abstract    Create/Update a Fake Node Entity
     * 
     * @param string    $Name           
     * @param Node      $Node       
     * 
     * @return \Nodes\FakerBundle\Entity\FakeNode
     */   
    public function setNode($Name, Node $Node)
    {
        $WsHost     = $_SERVER['SERVER_NAME'] . "/ws/soap";

        //====================================================================//
        // Connect to Router
        $Router =   $this->_container->get('router');
        //====================================================================//
        // Fetch First Site list
        $Sites    =     $this->_container->get('sonata.page.manager.site')->findAll();
        //====================================================================//
        // Setup Site
        $Router->getContext()->setSite( array_shift($Sites));
        $ServerPath = $Router->generate("splash_nodes_faker_soap_route", ["FakeId" => $Node->getIdentifier()]);;

        //====================================================================//
        // Try Loading Fake Node
        $FakeNode   = $this->_em
                ->getRepository('NodesFakerBundle:FakeNode')
                ->findOneByName($Name);
        //====================================================================//
        // Create Fake Node Entity if Needed
        if (!$FakeNode) {
            $FakeNode =     new FakeNode();
        }        
        
        $FakeNode
                ->setName($Name)
                ->setIdentifier($Node->getIdentifier())
                ->setUser($Node->getUser())
                ->setWsParameters(array(
                        "WsIdentifier"          =>  $Node->getIdentifier(),
                        "WsEncryptionKey"       =>  $Node->getCryptKey(),
                        "WsHost"                =>  $WsHost,
                        "WsMethod"              =>  "SOAP",
//                        "DefaultLanguage"       =>  "en_US",
//                        "Logging"               =>  True,
                        "localname"             =>  "Fake " . $Name,
                        "ServerHost"            =>  $_SERVER['SERVER_NAME'],
                        "ServerPath"            =>  $ServerPath,
                    ))
                ->setWsInformations()
            ;
        //====================================================================//
        // Persist Entity if Needed
        if (!$FakeNode->getId()) {
            $this->_em->persist($FakeNode);
        }            
        $this->_em->flush();
        return $FakeNode;
    }  
    
    /**
     * @abstract    Get a Fake Node By Name
     * 
     * @param string    $Name           
     * 
     * @return \Nodes\FakerBundle\Entity\FakeNode
     */       
    public function getByName($Name)
    {
        //==============================================================================
        // Load Object
        $FakeNode = $this->_em->getRepository('NodesFakerBundle:FakeNode')->findOneByName($Name);
        if (!$FakeNode) {
            throw new NotFoundHttpException('Unable to find Fake Node entity.');
        }
        return $FakeNode;
    } 
    
    /**
     * @abstract    Get a Fake Node By Identifier
     * 
     * @param string    $Identifier           
     * 
     * @return \Nodes\FakerBundle\Entity\FakeNode
     */       
    public function getByIdentifier($Identifier = Null)
    {
        if (is_null($Identifier)) {
            $Identifier = $this->_current;
        }        
        //==============================================================================
        // Load Object
        $FakeNode = $this->_em->getRepository('NodesFakerBundle:FakeNode')->findOneByIdentifier($Identifier);
        if (!$FakeNode) {
            throw new NotFoundHttpException('Unable to find Fake Node entity. (' . $Identifier . ")");
        }
        return $FakeNode;
    } 
    
    /**
     * @abstract    Get a First Fake Node
     * 
     * @return \Nodes\FakerBundle\Entity\FakeNode
     */       
    public function getFirst()
    {
        //==============================================================================
        // Load Object
        $FakeNodeList = $this->_em->getRepository('NodesFakerBundle:FakeNode')->findAll();
        if ( empty($FakeNodeList) ) {
            throw new NotFoundHttpException('Unable to find First Fake Node entity. ');
        }
        return array_shift($FakeNodeList);
    } 
    
    /**
     * @abstract    Load User Node Entity & Log user In
     * 
     * @param string    $Name       Node Name Or Node Id
     * 
     * @return \Nodes\CoreBundle\Entity\Node
     */   
    public function IdentifyNode($Name)
    {
        $NodeRepo  =   $this->_em->getRepository('NodesCoreBundle:Node');
        //==============================================================================
        // Load Node Object
        if (is_numeric($Name) ) {
            $Node = $NodeRepo->find($Name);        
        } else {
            $Node = $NodeRepo->findOneByName($Name);        
        }
        //==============================================================================
        // LogIn Node User
        if ($Node) {
            $Node->getUser()->logHimIn();
                    
            //====================================================================//
            // Dispatch Context For This Task
            $Context = new ActionContextEvent($Node,$Node->getUser()->getUserName(), "PhpUnit Tests...");
            $this->_container->get('event_dispatcher')
                    ->dispatch("openobject.action.context", $Context );
        }
        
        return  $Node;
    }      
    
    /**
     * @abstract    Load User Node Entity 
     * 
     * @param string    $Name       Node Name Or Node Id
     * 
     * @return \Nodes\CoreBundle\Entity\Node
     */   
    public function findNode($Name)
    {
        $NodeRepo  =   $this->_em->getRepository('NodesCoreBundle:Node');
        //==============================================================================
        // Load Node Object
        if (is_numeric($Name) ) {
            $Node = $NodeRepo->find($Name);        
        } else {
            $Node = $NodeRepo->findOneByName($Name);        
        }
        return  $Node;
    }      
    
//====================================================================//
//  OBJECTS OPERATIONS
//====================================================================//   
    
    /**
     * @abstract    Generate Fake Node Field 
     * 
     * @param string    $FieldSetType   
     * @param array     $Options     
     *   
     * 
     * @return array
     */   
    public function generateFieldsSet($FieldSetType, $Options = Null)
    {
        //====================================================================//
        // Load Field Builder Service
        $Builder    =   $this->_container->get("splash.nodes.faker.fields.builder");          
        $Builder->Init();        
        //==============================================================================
        // Populate Fields Array
        switch($FieldSetType) {
            case FakeNode::FAKE_OBJECTS_SHORT:
                
                //==============================================================================
                // Short Objects Fields Definition
                $Builder->add(SPL_T_VARCHAR,$Options);
                $Builder->add(SPL_T_BOOL,$Options);
                $Builder->add(SPL_T_INT,$Options);
                $Builder->add(SPL_T_BOOL,$Options);
                $Builder->add(SPL_T_INT,$Options);
                $Builder->add(SPL_T_VARCHAR,$Options);
                $Builder->add(SPL_T_EMAIL,$Options);
                $Builder->add(SPL_T_PHONE,$Options);
                
                break;
            
            
            case FakeNode::FAKE_OBJECTS_SIMPLE:
                
                //==============================================================================
                // Simple Objects Fields Definition
                $Builder->add(SPL_T_VARCHAR,$Options);
                $Builder->add(SPL_T_VARCHAR,$Options);
                $Builder->add(SPL_T_BOOL,$Options);
                $Builder->add(SPL_T_INT,$Options);
                $Builder->add(SPL_T_DOUBLE,$Options);
                $Builder->add(SPL_T_DATE,$Options);
                $Builder->add(SPL_T_DATETIME,$Options);
                $Builder->add(SPL_T_CURRENCY,$Options);
                $Builder->add(SPL_T_LANG,$Options);
                $Builder->add(SPL_T_STATE,$Options);
                $Builder->add(SPL_T_COUNTRY,$Options);
                $Builder->add(SPL_T_EMAIL,$Options);
                $Builder->add(SPL_T_URL,$Options);                
                $Builder->add(SPL_T_PHONE,$Options);
                $Builder->add(SPL_T_PRICE,$Options);
//                $Builder->add(SPL_T_ID,$Options);        // @TODO : Implement & Test Objects Ids 
//                $Builder->add(SPL_T_IMG,$Options);
//                $Builder->add(SPL_T_FILE,$Options);      // @TODO : Implement & Test Files Sync! 
                
                break;
            
            case FakeNode::FAKE_OBJECTS_SIMPLE2:
                
                //==============================================================================
                // Simple but Full Objects Fields Definition
                $Builder->add(SPL_T_VARCHAR,$Options);
                $Builder->add(SPL_T_VARCHAR,$Options);
                $Builder->add(SPL_T_BOOL,$Options);
                $Builder->add(SPL_T_INT,$Options);
                $Builder->add(SPL_T_DOUBLE,$Options);
                $Builder->add(SPL_T_DATE,$Options);
                $Builder->add(SPL_T_DATETIME,$Options);
                $Builder->add(SPL_T_CURRENCY,$Options);
                $Builder->add(SPL_T_COUNTRY,$Options);
                $Builder->add(SPL_T_EMAIL,$Options);
                $Builder->add(SPL_T_PHONE,$Options);
                $Builder->add(SPL_T_PRICE,$Options);
//                $Builder->add(SPL_T_ID,$Options);        // @TODO : Implement & Test Objects Ids 
//                $Builder->add(SPL_T_IMG,$Options);
//                $Builder->add(SPL_T_FILE,$Options);      // @TODO : Implement & Test Files Sync! 
                
                break;            
            
            case FakeNode::FAKE_OBJECTS_LIST:
                
                $Builder->add(SPL_T_VARCHAR,$Options);
                $Builder->add(SPL_T_BOOL,$Options);
                $Builder->add(SPL_T_INT,$Options);
                
                //==============================================================================
                // Simple List Objects Fields Definition
                $Builder->add(SPL_T_BOOL        . LISTSPLIT . SPL_T_LIST,$Options);
                $Builder->add(SPL_T_INT         . LISTSPLIT . SPL_T_LIST,$Options);
                $Builder->add(SPL_T_DOUBLE      . LISTSPLIT . SPL_T_LIST,$Options);
                $Builder->add(SPL_T_VARCHAR     . LISTSPLIT . SPL_T_LIST,$Options);
                $Builder->add(SPL_T_TEXT        . LISTSPLIT . SPL_T_LIST,$Options);
                $Builder->add(SPL_T_EMAIL       . LISTSPLIT . SPL_T_LIST,$Options);
                $Builder->add(SPL_T_PHONE       . LISTSPLIT . SPL_T_LIST,$Options);
                $Builder->add(SPL_T_DATE        . LISTSPLIT . SPL_T_LIST,$Options);
                $Builder->add(SPL_T_DATETIME    . LISTSPLIT . SPL_T_LIST,$Options);
                $Builder->add(SPL_T_LANG        . LISTSPLIT . SPL_T_LIST,$Options);
                $Builder->add(SPL_T_COUNTRY     . LISTSPLIT . SPL_T_LIST,$Options);
                $Builder->add(SPL_T_STATE       . LISTSPLIT . SPL_T_LIST,$Options);
                $Builder->add(SPL_T_URL         . LISTSPLIT . SPL_T_LIST,$Options);
                $Builder->add(SPL_T_MVARCHAR    . LISTSPLIT . SPL_T_LIST,$Options);
                $Builder->add(SPL_T_MTEXT       . LISTSPLIT . SPL_T_LIST,$Options);
                $Builder->add(SPL_T_PRICE       . LISTSPLIT . SPL_T_LIST,$Options); 
//                $Builder->add(SPL_T_IMG         . LISTSPLIT . SPL_T_LIST,$Options);
//                $Builder->add(SPL_T_FILE        . LISTSPLIT . SPL_T_LIST,$Options);
//                $Builder->add(SPL_T_ID        . LISTSPLIT . SPL_T_LIST,$Options);
                
                foreach( $this->getByIdentifier()->getObjectsTypes() as $ObjectType ) {
                    if ( in_array($ObjectType, $this->ObjectLinksFilter) ) {
                        continue;
                    }                 
                    //==============================================================================
                    // Simple but with Object Id Fields Definition
                    $Builder->add(Field::encodeIdField(SPL_T_ID,$ObjectType) . LISTSPLIT . SPL_T_LIST,$Options);
                }

                
                break;

            case FakeNode::FAKE_OBJECTS_IMG:
                
                $Builder->add(SPL_T_VARCHAR,$Options);
                $Builder->add(SPL_T_BOOL,$Options);
                $Builder->add(SPL_T_INT,$Options);
               
                //==============================================================================
                // Simple but with Image Fields Definition
                $Builder->add(SPL_T_IMG,$Options);
                
                break;             
            
            case FakeNode::FAKE_OBJECTS_FILE:
                
                $Builder->add(SPL_T_VARCHAR,$Options);
                $Builder->add(SPL_T_BOOL,$Options);
                $Builder->add(SPL_T_INT,$Options);
               
                //==============================================================================
                // Simple but with File Fields Definition
                $Builder->add(SPL_T_FILE,$Options);
                
                break;             
            
            case FakeNode::FAKE_OBJECTS_OBJECTID:
                
                $Builder->add(SPL_T_VARCHAR,$Options);
                $Builder->add(SPL_T_BOOL,$Options);
                $Builder->add(SPL_T_INT,$Options);

                foreach( $this->getByIdentifier()->getObjectsTypes() as $ObjectType ) {
                    if ( in_array($ObjectType, $this->ObjectLinksFilter) ) {
                        continue;
                    }
                    //==============================================================================
                    // Simple but with Object Id Fields Definition
                    $Builder->add(Field::encodeIdField(SPL_T_ID,$ObjectType),$Options);
                    $Builder->add(Field::encodeIdField(SPL_T_ID,$ObjectType),$Options);
                    $Builder->add(Field::encodeIdField(SPL_T_ID,$ObjectType),$Options);
                }
                
                break;             
            
            
            case FakeNode::FAKE_OBJECTS_ALL:
                
                $Builder->add(SPL_T_VARCHAR,$Options);
                $Builder->add(SPL_T_VARCHAR,$Options);
                $Builder->add(SPL_T_BOOL,$Options);
                $Builder->add(SPL_T_INT,$Options);
                $Builder->add(SPL_T_DOUBLE,$Options);
                $Builder->add(SPL_T_DATE,$Options);
                $Builder->add(SPL_T_DATETIME,$Options);
                $Builder->add(SPL_T_CURRENCY,$Options);
                $Builder->add(SPL_T_COUNTRY,$Options);
                $Builder->add(SPL_T_EMAIL,$Options);
//                $Builder->add(SPL_T_ID,$Options);
//                $Builder->add(SPL_T_IMG,$Options);
//                $Builder->add(SPL_T_FILE,$Options);
                
            
            
            case "full":
            
            
            
//            //====================================================================//
//            // Single Fields
//            array(SPL_T_BOOL),            
//            array(SPL_T_INT),            
//            array(SPL_T_DOUBLE),            
//            array(SPL_T_VARCHAR),            
//            array(SPL_T_TEXT),            
//            array(SPL_T_EMAIL),            
//            array(SPL_T_PHONE),            
//            array(SPL_T_DATE),            
//            array(SPL_T_DATETIME),            
//            array(SPL_T_LANG),            
//            array(SPL_T_COUNTRY),            
//            array(SPL_T_STATE),            
////            array(SPL_T_FILE),            
//            array(SPL_T_URL),            
//            array(SPL_T_IMG),            
////            array(SPL_T_MVARCHAR),            
////            array(SPL_T_MTEXT),            
//            array(SPL_T_PRICE),            
//            
//            //====================================================================//
//            // LIST Fields
//            array(SPL_T_BOOL . LISTSPLIT . SPL_T_LIST),            
//            array(SPL_T_INT . LISTSPLIT . SPL_T_LIST),            
//            array(SPL_T_DOUBLE . LISTSPLIT . SPL_T_LIST),            
//            array(SPL_T_VARCHAR . LISTSPLIT . SPL_T_LIST),            
//            array(SPL_T_TEXT . LISTSPLIT . SPL_T_LIST),            
//            array(SPL_T_EMAIL . LISTSPLIT . SPL_T_LIST),            
//            array(SPL_T_PHONE . LISTSPLIT . SPL_T_LIST),            
//            array(SPL_T_DATE . LISTSPLIT . SPL_T_LIST),            
//            array(SPL_T_DATETIME . LISTSPLIT . SPL_T_LIST),            
//            array(SPL_T_LANG . LISTSPLIT . SPL_T_LIST),            
//            array(SPL_T_COUNTRY . LISTSPLIT . SPL_T_LIST),            
//            array(SPL_T_STATE . LISTSPLIT . SPL_T_LIST),            
////            array(SPL_T_FILE),            
//            array(SPL_T_URL . LISTSPLIT . SPL_T_LIST),            
//            array(SPL_T_IMG . LISTSPLIT . SPL_T_LIST),            
////            array(SPL_T_MVARCHAR . LISTSPLIT . SPL_T_LIST),            
////            array(SPL_T_MTEXT . LISTSPLIT . SPL_T_LIST),            
//            array(SPL_T_PRICE . LISTSPLIT . SPL_T_LIST),              
                break;
            
            
        }
        
        //==============================================================================
        // Short Objects Meta Fields Definition
        $Builder->addMeta(WsSchema::META_OBJECTID);
        
        //====================================================================//
        // Publish Fields
        return $Builder->Publish();
    }      
    
//    /**
//     * @abstract    Generate Fake Node Object 
//     * 
//     * @param array     $FieldsList    
//     * 
//     * @return array
//     */   
//    public function generateObjectData($FieldsList)
//    {
//        return $this->formater->fakeObjectData($FieldsList);
//    }        
    
    /**
     * @abstract    Manual Add of an Object to Fake Node
     * @return FakeObject
     */
    public function addObject($FakeNode,$ObjectType,$Settings = Null)
    {
        //====================================================================//
        // Manually Add an Object To Fake Node
        $FieldsList     =   $FakeNode->getObjectFields($ObjectType);
        $FakeObject     =   $FakeNode->createObject($ObjectType);
        
        //====================================================================//
        // Setup Object Formater
        $this->formater->setSetting("Objects",$Settings?$Settings:$FakeNode->getObjectsFormaterList(Null, 10));
        
        //====================================================================//
        // Setup Fake Object 
        $FakeObject->setData($this->formater->fakeObjectData($FieldsList));       
        //====================================================================//
        // Save
        $this->_em->persist($FakeObject);
        $this->_em->flush();
        
        return $FakeObject;
    }      
    
    /**
     * @abstract    Random Select an FakeObject & Manual Modify it
     * @return FakeObject
     */
    public function randomizeObject($FakeNode,$ObjectType,&$FakeObject = Null)
    {
        if (is_null($FakeObject)) {
            //====================================================================//
            // Random Select of an Object
            $FakeObject = $FakeNode->getRandomObject($ObjectType);
            if (!$FakeObject) {
                return False;
            }
        }
        //====================================================================//
        // Manualy Modify FakeObject Data
        $FieldsList     =   $FakeNode->getObjectFields($ObjectType);
        //====================================================================//
        // Setup Object Formater
        $this->formater->setSetting("Objects",$FakeNode->getObjectsFormaterList(Null, 10));
        $FakeObject->setData($this->formater->fakeObjectData($FieldsList), True);
        //====================================================================//
        // Save
        $this->_em->flush();
        //====================================================================//
        // Return FakeObject
        return $FakeObject;
    }   
    
    /**
     * @abstract    Manualy Delete an FakeObject 
     */
    public function deleteObject($FakeNode,$ObjectType,$ObjectId)
    {
        //====================================================================//
        // Select Object
        $FakeObject = $FakeNode->getObject($ObjectType,$ObjectId);
        if (!$FakeObject) {
            return False;
        }
        //====================================================================//
        // Manually Delete FakeObject
        $this->_em->remove($FakeObject);
        //====================================================================//
        // Save
        $this->_em->flush();
        //====================================================================//
        // Return 
        return True;
    }       
    
    /**
     * @abstract    Random Select of an Object on a Fake Node
     *
     * @param   FakeNode    $FakeNode           Fake ?Node Object
     * @param   string      $ObjectType         Fake Object Type
     *
     * @return FakeObject
     */
    public function getRandomObject($FakeNode,$ObjectType)
    {
        //====================================================================//
        //Select Random Object in Fake Node
        $FakeObject     =   $FakeNode->getRandomObject($ObjectType);
        if ( $FakeObject ) {
            return  $FakeObject;
        }
        //====================================================================//
        //Add Random Object to Fake Node
        return $this->addObject($FakeNode,$ObjectType);
    }     
    
//====================================================================//
//  EXECUTE CLIENT CORE OPERATIONS
//====================================================================//
    
    /**
     * @abstract    Execute Fake Client Action
     * 
     * @param string    $Function           
     * @param string    $Identifier           
     * @param array     $Parameters           
     * 
     * @return array
     */ 
    public function doClientAction(string $Function, string $Identifier = Null, array $Parameters = array())
    {
        //====================================================================//
        // Setup Current Fake Node
        if ($Identifier) {
            $this->setCurrentFaker($Identifier);
        }
        //====================================================================//
        // Activate Debug Mode
        if (!defined('SPLASH_DEBUG')) {
            define("SPLASH_DEBUG"           ,   True);         
        }
        //====================================================================//
        // Reboot Splash Server
        Splash::reboot();        
        //====================================================================//
        // Execute Splash Server Action
        $Result = call_user_func_array( Splash::class . "::" . $Function, $Parameters);
        if(!$Result && !$this->isExpectErrors()) {
            //====================================================================//
            // Echo Module Messages
            var_dump( Splash::log()->getRawLog(False));        
            echo Splash::log()->getHtmlLog(True);        
        }
        Splash::log()->cleanLog();      
        //====================================================================//
        // Return Result
        return $Result;     
    } 

    /**
     * @abstract    Execut Fake Client Ping Request
     * 
     * @param string    $Identifier           
     * 
     * @return array
     */ 
    public function doClientPing($Identifier = Null)
    {
        //====================================================================//
        // Ping Splash Server
        return  $this->doClientAction("Ping", $Identifier);
    }    
    
    /**
     * @abstract    Execute Fake Client Connect Request
     * 
     * @param string    $Identifier           
     * 
     * @return array
     */ 
    public function doClientConnect($Identifier = Null)
    {
        //====================================================================//
        // Connect Splash Server
        return  $this->doClientAction("Connect", $Identifier);
    }      
    
    /**
     * @abstract    Execute Fake Client SelfTest Request
     * 
     * @param string    $Identifier           
     * 
     * @return array
     */ 
    public function doClientSelfTest($Identifier = Null)
    {
        //====================================================================//
        // SelfTest Splash Server
        return  $this->doClientAction("SelfTest", $Identifier);
    }   
    
    
    /**
     * @abstract    Execute Fake Client Read Server Informations Request
     * 
     * @param string    $Identifier           
     * 
     * @return array
     */ 
    public function doClientInformations($Identifier = Null)
    {
        //====================================================================//
        // SelfTest Splash Server
        return  $this->doClientAction("Informations", $Identifier);
    }       

//====================================================================//
//  EXECUTE CLIENT OBJECTS OPERATIONS
//====================================================================//

    /**
     * @abstract    Execute Fake Client Action
     * 
     * @param string    $Function           
     * @param string    $ObjectType           
     * @param string    $Identifier           
     * @param array     $Parameters           
     * 
     * @return array
     */ 
    public function doClientObjectAction($Function, $ObjectType, $Identifier = Null, $Parameters = array())
    {
        //====================================================================//
        // Setup Current Fake Node
        if ($Identifier) {
            $this->setCurrentFaker($Identifier);
        }
        //====================================================================//
        // Activate Debug Mode
        if (!defined('SPLASH_DEBUG')) {
            define("SPLASH_DEBUG"           ,   True);         
        }
        //====================================================================//
        // Reboot Splash Server
        Splash::reboot();        
        //====================================================================//
        // Execute Splash Server Action
        $ObjectClass    =   Splash::object($ObjectType);
        $Result = call_user_func_array( array($ObjectClass,$Function) , $Parameters);
        if(!$Result && !$this->isExpectErrors()) {
            //====================================================================//
            // Echo Module Messages
            echo Splash::log()->getHtmlLog(True);        
        }
        Splash::log()->cleanLog();      
        //====================================================================//
        // Return Result
        return $Result;     
    }    
    
    /**
     * @abstract    Execute Fake Client List Available Objects Request
     * 
     * @param string    $Identifier           
     * 
     * @return array
     */ 
    public function doClientObjects($Identifier = Null)
    {
        //====================================================================//
        // SelfTest Splash Server
        return  $this->doClientAction("Objects", $Identifier);
    }       
    
    /**
     * @abstract    Execute Fake Client List Available Objects Request
     * 
     * @param string    $Identifier           
     * 
     * @return array
     */ 
    public function doClientObjectFields($ObjectType, $Identifier = Null)
    {
        //====================================================================//
        // SelfTest Splash Server
        return  $this->doClientObjectAction("Fields", $ObjectType, $Identifier);
    }         
    
    /**
     * @abstract    Execute Fake Client List Available Objects Request
     * 
     * @param   string      $ObjectType        ObjectType Name  
     * @param   string      $ObjectId          ObjectId  
     * @param   array       $ObjectFields      Object Fields List  
     * @param   string      $Identifier           
     * 
     * @return array
     */ 
    public function doClientObjectGet($ObjectType, $ObjectId, $ObjectFields = Null, $Identifier = Null)
    {
        //====================================================================//
        // Prepare Field List if Needed
        if ( !$ObjectFields ) {
            $ObjectFields = $this->getByIdentifier()->getObjectFields($ObjectType);
        }
   
        //====================================================================//
        // Execute Get Action on Splash Server
        return  $this->doClientObjectAction("Get", $ObjectType, $Identifier, array(
                "id"    =>      $ObjectId,
                "list"  =>      $this->formater->reduceFieldList($ObjectFields),
            ));
    }    
    
    /**
     * @abstract    Execute Fake Client List Available Objects Request
     * 
     * @param   string      $ObjectType         ObjectType Name  
     * @param   string      $ObjectId           ObjectId  
     * @param   array       $ObjectData         Object Data List  
     * @param   string      $Identifier           
     * 
     * @return array
     */ 
    public function doClientObjectSet($ObjectType, $ObjectId, $ObjectData = array(), $Identifier = Null)
    {
        //====================================================================//
        // Execute Get Action on Splash Server
        return  $this->doClientObjectAction("Set", $ObjectType, $Identifier, array(
                "id"    =>      $ObjectId,
                "list"  =>      $ObjectData,
            ));
    }     
    
    /**
     * @abstract    Execute Fake Client List Available Objects Request
     * 
     * @param   string      $ObjectType        ObjectType Name  
     * @param   string      $ObjectId          ObjectId  
     * @param   string      $Identifier           
     * 
     * @return array
     */ 
    public function doClientObjectDelete($ObjectType, $ObjectId, $Identifier = Null)
    {
        //====================================================================//
        // Execute Delete Action on Splash Server
        return  $this->doClientObjectAction("Delete", $ObjectType, $Identifier, array(
                "id"    =>      $ObjectId,
            ));
    }    
    
    /**
     * @abstract    Execute Fake Client List Available Objects Request
     * 
     * @param   string      $ObjectType         ObjectType Name  
     * @param   string      $ObjectId           ObjectId  
     * @param   array       $Action             Object Action Name
     * @param   string      $Identifier           
     * 
     * @return array
     */ 
    public function doClientObjectCommit($ObjectType, $ObjectId, $Action, $Identifier = Null)
    {
        //====================================================================//
        // Execute Commit Action on Splash Server
        return  $this->doClientAction("Commit", $Identifier, array(
                "ObjectType"    =>      $ObjectType, 
                "local"         =>      $ObjectId,
                "action"        =>      $Action,
                "user"          =>      "Splash",
                "comment"       =>      "PhpUnit Test"
            ));
    }        
    
//====================================================================//
//  EXECUTE CLIENT FILE OPERATIONS
//====================================================================//
    
    /**
     * @abstract    Execute Fake Client File Action
     * 
     * @param string    $Function           
     * @param string    $Identifier           
     * @param array     $Parameters           
     * 
     * @return array
     */ 
    public function doClientFileAction($Function, $Identifier = Null, $Parameters = array())
    {
        //====================================================================//
        // Setup Current Fake Node
        if ($Identifier) {
            $this->setCurrentFaker($Identifier);
        }
        //====================================================================//
        // Activate Debug Mode
        if (!defined('SPLASH_DEBUG')) {
            define("SPLASH_DEBUG"           ,   True);         
        }
        //====================================================================//
        // Reboot Splash Server
        Splash::reboot();        
        //====================================================================//
        // Execute Splash Server Action
        $ObjectClass    =   Splash::file();
        $Result = call_user_func_array( array($ObjectClass,$Function) , $Parameters);
        if(!$Result && !$this->isExpectErrors()) {
            //====================================================================//
            // Echo Module Messages
            echo Splash::log()->getHtmlLog(True);        
        }
        Splash::log()->cleanLog();      
        //====================================================================//
        // Return Result
        return $Result;     
    }
    
    
    /**
     * @abstract    Execute Fake Client isFile Action
     * 
     * @param string    $FilePath           
     * @param string    $FileMd5           
     * @param string    $Identifier           
     * 
     * @return array
     */ 
    public function doClientIsFileAction(string $FilePath = null, string $FileMd5 = null, string $Identifier = Null)
    {
        //====================================================================//
        // Execute Commit Action on Splash Server
        return  $this->doClientFileAction("isFile", $Identifier, array(
                "Path"          =>      $FilePath, 
                "Md5"           =>      $FileMd5,
            ));        
 
    }   
    
    /**
     * @abstract    Execute Fake Client ReadFile Action
     * 
     * @param string    $FilePath           
     * @param string    $FileMd5           
     * @param string    $Identifier           
     * 
     * @return array
     */ 
    public function doClientReadFileAction(string $FilePath = null, string $FileMd5 = null, string $Identifier = Null)
    {
        //====================================================================//
        // Execute Commit Action on Splash Server
        return  $this->doClientFileAction("ReadFile", $Identifier, array(
                "Path"          =>      $FilePath, 
                "Md5"           =>      $FileMd5,
            ));        
 
    }     
    
    /**
     * @abstract    Execute Fake Client WriteFile Action
     * 
     * @param string    $FilePath           
     * @param string    $FileMd5           
     * @param string    $FileRaw           
     * @param string    $Identifier           
     * 
     * @return array
     */ 
    public function doClientWriteFileAction(string $FilePath, string $FileMd5, string $FileRaw, string $Identifier = Null)
    {
        //====================================================================//
        // Execute Commit Action on Splash Server
        return  $this->doClientFileAction("WriteFile", $Identifier, array(
                "Dir"           =>      dirname($FilePath) . "/", 
                "File"          =>      basename($FilePath), 
                "Md5"           =>      $FileMd5,
                "Raw"           =>      $FileRaw,
            ));        
 
    }         
    
    /**
     * @abstract    Execute Fake Client WriteFile Action
     * 
     * @param string    $FilePath           
     * @param string    $FileMd5           
     * @param string    $Identifier           
     * 
     * @return array
     */ 
    public function doClientDeleteFileAction($FilePath, $FileMd5, $Identifier = Null)
    {
        //====================================================================//
        // Execute Commit Action on Splash Server
        return  $this->doClientFileAction("DeleteFile", $Identifier, array(
                "Path"          =>      $FilePath, 
                "Md5"           =>      $FileMd5,
            ));        
 
    }     
    
    /**
     * @abstract    Execute Fake Client File Action
     * 
     * @param string    $FileId           
     * @param string    $FileMd5           
     * @param string    $Identifier           
     * @param array     $Parameters           
     * 
     * @return array
     */ 
//    public function doClientReadFileAction($FileId, $FileMd5, $Identifier = Null)
//    {
//        //====================================================================//
//        // Setup Current Fake Node
//        if ($Identifier) {
//            $this->setCurrentFaker($Identifier);
//        }
//        //====================================================================//
//        // Activate Debug Mode
//        if (!defined('SPLASH_DEBUG')) {
//            define("SPLASH_DEBUG"           ,   True);         
//        }
//        //====================================================================//
//        // Create Splash Server
//        include_once dirname(__DIR__) . self::FAKE_MODULE_PATH . self::FAKE_MODULE_FILE;
//        //====================================================================//
//        // Reboot Splash Server
//        \Splash::reboot();        
//        //====================================================================//
//        // Execute Splash Server Action
//        $Result = \Splash::ReadFile($FileId, $FileMd5);
//        if(!$Result) {
//            //====================================================================//
//            // Echo Module Messages
//            echo \Splash::log()->GetHtmlLog(True);        
//        }
//        \Splash::log()->CleanLog();      
//        //====================================================================//
//        // Return Result
//        return $Result;     
//    }    
    
}