<?php

namespace Sanderdekroon\Parlant\Builder;

use BadMethodCallException;
use InvalidArgumentException;
use Sanderdekroon\Parlant\Container;
use Sanderdekroon\Parlant\Adapter\Query as QueryAdapter;
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

    protected $queryAdapter;

    protected $configuration;


    public function __construct(Container $container = null)
    {
        $this->grammar = new PosttypeGrammar; // Replace via DI
        $this->compiler = new PosttypeCompiler($this->getGrammar()); // Replace via DI
        $this->queryAdapter = new QueryAdapter(); //Replace via DI
        $this->bindings = $container ?: new Container;
    }


    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return $this->$name(...$arguments);
        }

        if ($this->isTranslatable($name)) {
            return $this->translateAndSetQueryBinding($name, $arguments);
        }

        throw BadMethodCallException("Method {$name} does not exists on ".__CLASS__);
    }


    protected function getQueryAdapter()
    {
        return $this->queryAdapter;
    }


    protected function isTranslatable($name)
    {
        return $this->getQueryAdapter()->isTranslatable($name);
    }


    protected function translateAndSetQueryBinding($name, $value)
    {
        list($queryKey, $queryValue) = $this->getQueryAdapter()->translate($name, $value);
        $this->setBinding($queryKey, $queryValue);

        return $this;
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


    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause.
        if (is_array($column)) {
            return $this->addArrayOfWheres($column);
        }

        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        list($value, $operator) = $this->prepareValueAndOperator(
            $value,
            $operator,
            (func_num_args() == 2 || is_null($value)) //Is this the best solution?
        );

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            list($value, $operator) = [$operator, '='];
        }


        // If the value is "null", we will just assume the developer wants to add a
        // where null clause to the query. So, we will allow a short-cut here to
        // that method for convenience so the developer doesn't have to check.
        // if (is_null($value)) {
        //     return $this->whereNull($column, $boolean, $operator != '=');
        // }

        // Now that we are working with just a simple query we can put the elements
        // in our array and add the query binding to our array of bindings that
        // will be bound to each SQL statements when it is finally executed.
        $type = 'Basic';

        $this->appendBinding('wheres', compact(
            'type',
            'column',
            'operator',
            'value',
            'boolean'
        ));

        return $this;
    }

    /**
     * Add an array of where statements. The array should contain a numeric array
     * where the values can be used as arguments for the where() method.
     * @param array $wheres
     */
    protected function addArrayOfWheres($wheres)
    {
        foreach ($wheres as $where) {
            list($column, $operator, $value, $boolean) = $this->extractWhereValuesFromArray($where);
            $this->where($column, $operator, $value, $boolean);
        }

        return $this;
    }

    
    protected function extractWhereValuesFromArray($array)
    {
        return [
            isset($array[0]) ? $array[0] : null,
            isset($array[1]) ? $array[1] : null,
            isset($array[2]) ? $array[2] : null,
            isset($array[3]) ? $array[3] : null,
        ];
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
