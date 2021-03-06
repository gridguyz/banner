<?php

namespace Grid\Banner\Model\Banner\Structure;

use Traversable;
use AppendIterator;
use Zork\Factory\AdapterInterface;
use Zork\Model\MapperAwareInterface;
use Zork\Model\Exception\LogicException;
use Zork\Model\Structure\StructureAbstract;
use Grid\Banner\Model\Banner\StructureInterface;

/**
 * ProxyAbstract
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
abstract class ProxyAbstract
       extends StructureAbstract
    implements AdapterInterface,
               StructureInterface,
               MapperAwareInterface
{

    /**
     * Banner type
     *
     * @var string
     * @abstract
     */
    protected static $type;

    /**
     * View-partial
     *
     * @var string
     * @abstract
     */
    protected static $viewPartial;

    /**
     * Proxy base object
     *
     * @var \Grid\Banner\Model\Banner\Structure\ProxyBase
     */
    private $proxyBase;

    /**
     * Constructor
     *
     * @param   array $data
     * @throws  \Zork\Model\Exception\LogicException if type does not match
     */
    public function __construct( $data = array() )
    {
        parent::__construct( $data );
        $proxyBase = $this->proxyBase;

        if ( empty( $proxyBase->type ) )
        {
            $proxyBase->type = static::$type;
        }
        else if ( ! empty( static::$type ) &&
                  static::$type !== $proxyBase->type )
        {
            throw new LogicException( 'Type does not match' );
        }
    }

    /**
     * Set option enhanced to be able to set id
     *
     * @param   string  $key
     * @param   mixed   $value
     * @return  \Grid\Banner\Model\Banner\Structure\ProxyAbstract
     */
    public function setOption( $key, $value )
    {
        if ( 'id' == $key )
        {
            if ( empty( $this->proxyBase ) )
            {
                $this->proxyBase = new ProxyBase( array(
                    'id' => $value,
                ) );
            }
            else
            {
                $this->proxyBase->setOption( $key, $value );
            }

            return $this;
        }

        return parent::setOption( $key, $value );
    }

    /**
     * Set options
     *
     * @param   mixed $options
     * @return  \Grid\Banner\Model\Banner\Structure\ProxyAbstract
     */
    public function setOptions( $options )
    {
        if ( $options instanceof Traversable )
        {
            $options = iterator_to_array( $options );
        }

        if ( isset( $options['proxyBase'] ) )
        {
            $this->setProxyBase( $options['proxyBase'] );
            unset( $options['proxyBase'] );
        }

        return parent::setOptions( $options );
    }

    /**
     * Get proxy base object
     *
     * @return  \Grid\Banner\Model\Banner\Structure\ProxyBase
     */
    public function setProxyBase( ProxyBase $proxyBase )
    {
        $this->proxyBase = $proxyBase;
        return $this;
    }

    /**
     * Get service-locator
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->proxyBase
                    ->getServiceLocator();
    }

    /**
     * Get the mapper object
     *
     * @return \Grid\Banner\Model\Banner\Mapper
     */
    public function getMapper()
    {
        return $this->proxyBase
                    ->getMapper();
    }

    /**
     * Set the mapper object
     *
     * @param   \Grid\Banner\Model\Banner\Mapper $mapper
     * @return  \Grid\Banner\Model\Banner\Structure\ProxyAbsract
     */
    public function setMapper( $mapper = null )
    {
        $this->proxyBase
             ->setMapper( $mapper );

        return $this;
    }

    /**
     * Save me
     *
     * @return int Number of affected rows
     */
    public function save()
    {
        return $this->getMapper()
                    ->save( $this );;
    }

    /**
     * Delete me
     *
     * @return int Number of affected rows
     */
    public function delete()
    {
        return $this->getMapper()
                    ->delete( $this );
    }

    /**
     * Get the proxy-base
     *
     * @return  \Grid\Banner\Model\Banner\Structure\ProxyBase
     */
    protected function & proxyBase()
    {
        return $this->proxyBase;
    }

    /**
     * Get ID of the paragraph
     *
     * @return  int|null
     */
    public function getId()
    {
        return $this->proxyBase->id;
    }

    /**
     * Get type of the paragraph
     *
     * @return  string|null
     */
    public function getType()
    {
        return $this->proxyBase->type;
    }

    /**
     * Get label of the paragraph
     *
     * @return  string|null
     */
    public function getLabel()
    {
        return $this->proxyBase->label;
    }

    /**
     * Get target of the paragraph
     *
     * @return  string|null
     */
    public function getTarget()
    {
        return $this->proxyBase->target;
    }

    /**
     * Set type of the paragraph
     *
     * @return  string|null
     * @thorws  \Zork\Model\Exception\LogicException
     */
    public function setType( $type )
    {
        if ( empty( static::$type ) )
        {
            $this->proxyBase->type = $type;
        }
        elseif ( static::$type != $type )
        {
            throw new LogicException( 'Cannot alter type after creation' );
        }

        return $this;
    }

    /**
     * Returns the base iterator (only basic properties)
     *
     * @return  \Zork\Model\Structure\StructureIterator
     */
    public function getBaseIterator()
    {
        return $this->proxyBase
                    ->getIterator();
    }

    /**
     * Returns the properties iterator (only additional properties)
     *
     * @return  \Zork\Model\Structure\StructureIterator
     */
    public function getPropertiesIterator()
    {
        return parent::getIterator();
    }

    /**
     * Get iterator
     *
     * @return  \AppendIterator
     */
    public function getIterator()
    {
        $result = new AppendIterator;
        $result->append( $this->getBaseIterator() );
        $result->append( $this->getPropertiesIterator() );
        return $result;
    }

    /**
     * Getter for view-partial
     *
     * @return  string
     */
    public function getViewPartial()
    {
        return static::$viewPartial;
    }

    /**
     * Return true if and only if $options accepted by this adapter
     * If returns float as likelyhood the max of these will be used as adapter
     *
     * @param array $options;
     * @return float
     */
    public static function acceptsOptions( array $options )
    {
        return isset( $options['type'] ) && $options['type'] === static::$type;
    }

    /**
     * Return a new instance of the adapter by $options
     *
     * @param array $options;
     * @return \Paragraph\Model\Paragraph\Structure\ProxyAbstract
     */
    public static function factory( array $options = null )
    {
        return new static( $options );
    }

}
