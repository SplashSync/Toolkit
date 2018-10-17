<?php

namespace Splash\Connectors\FakerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @abstract    Splash Fake/Testing Objects 
 *
 * @ORM\Table(name="splash__faker__objects")
 * @ORM\Entity(repositoryClass="Splash\Connectors\FakerBundle\Repository\FakeObjectRepository")
 */
class FakeObject
{
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var string
     */
    private $condition;

    //==============================================================================
    //      FAKER OBJECT DATA
    //==============================================================================   
    
    /**
     * @abstract    Fake Object Type Name
     * 
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;  

    /**
     * @abstract    Fake Object Identifier
     * 
     * @var string
     *
     * @ORM\Column(name="identifier", type="string", length=255)
     */
    private $identifier;  

    /**
     * @abstract    Fake Object Data
     * 
     * @var string
     *
     * @ORM\Column(name="data", type="object")
     */
    private $data;  
    
    //==============================================================================
    //      DATA OPERATIONS  
    //==============================================================================   
    
    public function __toString()
    {
        return $this->getType() . "@" . $this->getIdentifier();
    }      
    
    //==============================================================================
    //      GETTERS & SETTERS 
    //==============================================================================   
    

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return FakeObject
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     *
     * @return FakeObject
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set Field
     *
     * @param \stdClass $Data
     *
     * @return FakeObject
     */
    public function setField($Id, $Data)
    {
        $this->data[$Id] = $Data;

        return $this;
    }
    
    /**
     * Set data
     *
     * @param   array   $Data
     * @param   bool    $ProtectMeta
     *
     * @return  self
     */
    public function setData($Data, $ProtectMeta = False)
    {
        //====================================================================//
        // Raw Write of Object Data
        if ( !$ProtectMeta ) {
            $this->data = $Data;
            return $this;
        }
        //====================================================================//
        // Meta Data Protected Write of Object Data
        $Fields = $this->getNode()->getObjectFields($this->getType());

        //====================================================================//
        // Write Data One by One
        foreach ($Data as $FieldId => $FieldData) {

            //====================================================================//
            // Verify Field is Not a Meta Field
            $IsMeta = False;
            foreach ($Fields as $Field) {
                if ( $Field->id !== $FieldId ) {
                    continue;
                }
                if ( \OpenObject\WsSchemasBundle\Entity\WsSchema::isMetaTag($Field->tag) ) {
                    $IsMeta = True;
                }
            }
            if ( $IsMeta ) {
                continue;
            }
            //====================================================================//
            // Write Field Data
            $this->data[$FieldId] = $FieldData;

        }
        return $this;
    }

    /**
     * Get data
     *
     * @param   string  $FieldId        Field Name or Null
     * 
     * @return array
     */
    public function getData( $FieldId = Null )
    {
        if ( $FieldId ) {
            return $this->data[$FieldId];
        } 
        return $this->data;
    }

    /**
     * Set condition
     *
     * @param string $condition
     *
     * @return self
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * Get condition
     *
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }
}
