<?php

namespace Sanderdekroon\Parlant\Builder;

use InvalidArgumentException;
use Sanderdekroon\Parlant\Container;
use Sanderdekroon\Parlant\Grammar\PosttypeGrammar;
use Sanderdekroon\Parlant\Compiler\PosttypeCompiler;
use Sanderdekroon\Parlant\Configurator\ParlantConfigurator;
use Sanderdekroon\Parlant\Configurator\ConfiguratorInterface;

class PosttypeBuilder implements BuilderInterface
{
    use BuildsQueries, QueriesMeta, QueriesTaxonomies;


    protected $grammar;
    
    protected $compiler;
    protected $bindings;

    protected $configuration;


    public function __construct(Container $container = null)
    {
        $this->grammar = new PosttypeGrammar; // Replace via DI
        $this->compiler = new PosttypeCompiler($this->getGrammar()); // Replace via DI
        $this->bindings = $container ?: new Container;
    }


    public function configure($configuration)
    {
        // If the supplied configuration is an instance of the ConfiguratorInterface
        // we can add it directly to the PosttypeBuilder. This way a developer
        // can supply their own configurator implementation.
        if ($configuration instanceof ConfiguratorInterface) {
            return $this->configuration = $configuration;
        }

        // If the developer wants to add additional configuration but has not
        // supplied an instance of the ConfiguratorInterface, we'll create
        // a new instance of our own implementation of the configurator.
        if (is_null($this->getConfiguration())) {
            $this->configuration = new ParlantConfigurator;
        }

        return $this->configuration->add($configuration);
    }


    public function setConfig($key, $value)
    {
        if ($this->requiresConfiguration()) {
            $this->applyDefaultConfiguration();
        }

        return $this->updateConfiguration($key, $value);
    }


    protected function updateConfiguration($key, $value)
    {
        $this->getConfiguration()->add($key, $value);

        return $this;
    }

    /**
     * Determine if the builder requires additional configuration.
     * @return bool
     */
    protected function requiresConfiguration()
    {
        return is_null($this->getConfiguration());
    }

    /**
     * Fill the configuration property with an instance of our ParlantConfigurator.
     * @return ParlantConfigurator
     */
    protected function applyDefaultConfiguration()
    {
        return $this->configuration = new ParlantConfigurator;
    }

    /**
     * Set the posttype the developer is querying.
     * @param  string $posttype
     * @return $this
     */
    public function type($posttype)
    {
        // Since this is the entry method (for now), we'll assume the developer allready
        // has supplied some form of configuration. If nothing is found, we'll create a
        // new instance of our ParlantConfigurator which sets some default settings.
        if ($this->requiresConfiguration()) {
            $this->applyDefaultConfiguration();
        }

        $this->setBinding('post_type', $posttype);
        return $this;
    }

    /**
     * Start a new Where clause. This simply adds the arguments to the outer array.
     * @param  string|array $column
     * @param  string|null $operator
     * @param  string|null $value
     * @return $this
     */
    public function where($column, $operator = null, $value = null)
    {
        $clause = new WhereClause($this->getGrammar());

        foreach ($clause->build($column, $operator, $value) as $where) {
            $this->appendBinding('wheres', $where);
        }

        return $this;
    }

    /**
     * Limit the number of posts to return to the $number
     * @param  int $number
     * @return $this
     */
    public function limit($number)
    {
        $this->setBinding('limit', (int)$number);
        return $this;
    }

    /**
     * Set the post offset.
     * @param  int $number
     * @return $this
     */
    public function offset($number)
    {
        $this->setBinding('offset', (int)$number);
        return $this;
    }

    /**
     * Prepare the value and operator. If $useDefault is true, return the default operator (=)
     * Throws an exception if the operator is not supported with the current grammer.
     * @param  mixed        $value
     * @param  string       $operator
     * @param  boolean      $useDefault
     * @throws InvalidArgumentException
     * @return array
     * @todo  make deprecated
     */
    protected function prepareValueAndOperator($value, $operator, $useDefault = false, $termDefault = false)
    {
        if ($useDefault) {
            return [$operator, $termDefault ? 'IN' : '='];
        }

        if ($this->invalidOperator($operator) && !is_null($value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        return [$value, $operator];
    }

    protected function getGrammar()
    {
        return $this->grammar;
    }

    protected function getCompiler()
    {
        return $this->compiler;
    }

    protected function getBindings()
    {
        return $this->bindings;
    }

    protected function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Determine if an operator is invalid or unsupported
     * @param  string $operator
     * @return bool
     * @todo  make deprecated
     */
    private function invalidOperator($operator)
    {
        return !in_array($operator, $this->getGrammar()->getOperators());
    }

    /**
     * Set an query binding
     * @param  string    $key   @todo validate if the $key is valid with the grammar
     * @param  mixed     $data
     * @return bool
     */
    private function setBinding($key, $data)
    {
        return $this->getBindings()->bind($key, $data);
    }

    /**
     * Append an query binding
     * @param  string   $key  @todo validate if the $key is valid with the grammar
     * @param  mixed    $data
     * @return bool
     */
    private function appendBinding($key, $data)
    {
        return $this->getBindings()->append($key, $data);
    }


    private function getBinding($key)
    {
        return $this->getBindings()->get($key);
    }
}
