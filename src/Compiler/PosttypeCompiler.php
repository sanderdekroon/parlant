<?php

namespace Sanderdekroon\Parlant\Compiler;

use Closure;
use Exception;
use Sanderdekroon\Parlant\Container;
use Sanderdekroon\Parlant\Grammar\PosttypeGrammar;
use Sanderdekroon\Parlant\Formatter\FormatterInterface;
use Sanderdekroon\Parlant\Configurator\ConfiguratorInterface;

class PosttypeCompiler
{
    use CompilesMeta, CompilesTaxonomies;

    protected $grammar;
    protected $arguments = [];
    protected $container;
    protected $configuration;

    public function __construct(PosttypeGrammar $grammar)
    {
        $this->grammar = $grammar;
    }


    public function setDefaults(array $defaults)
    {
        $validArguments = $this->grammar->getArguments();

        foreach ($defaults as $name => $default) {
            if (in_array($name, $validArguments)) {
                $this->arguments[$name] = $default;
            }
        }

        return $this;
    }

    /**
     * Bind the bindings to this class.
     * @param  Container $bindings
     * @return $this
     */
    public function bind(Container $bindings)
    {
        $this->bindings = $bindings;
        return $this;
    }

    /**
     * Pass the configuration to this class
     * @param  ConfiguratorInterface $configuration
     * @return $this
     */
    public function withConfiguration(ConfiguratorInterface $configuration)
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * Build the query arguments and pass it to an formatter.
     * @return mixed
     */
    public function build()
    {
        $this->applyConfiguration();
        $this->addArgument(['post_type' => $this->getPosttype()]);

        $bindings = $this->bindings->all();

        foreach ($bindings as $name => $binding) {
            if ($this->needsToCompile($name)) {
                $this->compileType($name, $binding);
            }

            if ($this->validArgument($name)) {
                $this->addArgument($name, $binding); //@todo validation/sanitation
            }
        }

        $formatter = $this->getFormatter();

        return $formatter->output($this->arguments);
    }

    /**
     * Check if the supplied name is a valid query argument
     * @param  string $name
     * @return bool
     */
    protected function validArgument($name)
    {
        return in_array($name, $this->grammar->getArguments());
    }

    /**
     * Check if the supplied query name needs compiling.
     * @param  string $name
     * @return bool
     */
    protected function needsToCompile($name)
    {
        return in_array($name, $this->grammar->getQueryTypes());
    }

    /**
     * Returns the output formatter
     * @return FormatterInterface
     */
    protected function getFormatter()
    {
        $type = $this->getReturnType();

        // The developer has supplied an function that has to be used as a formatter.
        // Note: this is not preferred, as we cannot check the existance of an
        // output method after the function call.
        if ($type instanceof Closure) {
            return call_user_func($type);
        }

        // If it's not an closure, we're expecing a fully namespaced classname. We'll
        // create a new instance of that class and check if it implements the
        // FormatterInterface. If it does, we'll return the instance.
        if (!class_exists($type)) {
            throw new Exception('Could not compile: formatter could not be instantiated.'); //Replace with CompilerException
        }

        $formatter = new $type;

        if (!$formatter instanceof FormatterInterface) {
            throw new Exception('Could not compile: formatter does not implement FormatterInterface.');
        }

        return $formatter;
    }

    /**
     * Get the configured return type.
     * @return string|closure
     */
    private function getReturnType()
    {
        $type = $this->configuration->get('return');

        if (is_callable($type)) {
            return $type;
        }

        if (array_key_exists($type, $this->grammar->getFormatters())) {
            return $this->grammar->getFormatter($type);
        }

        return $type;
    }

    /**
     * Before the arguments are compiled and added, apply the configuration paramaters.
     * @return $this
     */
    protected function applyConfiguration()
    {
        foreach ($this->grammar->getArguments() as $argument) {
            if (!$this->configuration->has($argument)) {
                continue;
            }

            $this->addArgument($argument, $this->configuration->get($argument));
        }

        return $this;
    }

    /**
     * Get the compiling method. Basically prepends 'compile'.
     * @param  string $type
     * @return string       Method name, or false if it does not exist.
     */
    private function getCompilingMethod($type)
    {
        $name = 'compile'.ucfirst($type);

        return method_exists($this, $name) ? $name : false;
    }

    /**
     * Compile the supplied type to valid query arguments.
     * @param  string $type
     * @param  array $arguments
     * @return array
     */
    private function compileType($type, $arguments)
    {
        $method = $this->getCompilingMethod($type);

        if ($method === false) {
            throw new \BadMethodCallException('Invalid querytype requested. Method compile'.ucfirst((string)$type).' does not exist!');
        }

        return $this->$method($arguments);
    }

    /**
     * Compile all the where's to valid query arguments
     * @param  array $wheres
     * @return array
     */
    protected function compileWheres($wheres)
    {
        if (empty($wheres) || !is_array($wheres)) {
            return;
        }
        
        foreach ($wheres as $where) {
            $this->addArgument([$where['column'] => $where['value']]);
        }
    }

    /**
     * Compile limit to valid query arguments. Basically transforms limit to posts_per_page.
     * @return [type] [description]
     */
    protected function compileLimit($limit)
    {
        $this->addArgument('posts_per_page', (int)$limit);
    }

    /**
     * Get the requested post type string.
     * @return string
     */
    protected function getPosttype()
    {
        $posttype = $this->bindings->get('post_type');
        if ($this->wantsAllPosttypes($posttype)) {
            return 'any';
        }

        return $posttype;
    }

    /**
     * Determine if the developer wants all posttypes or not.
     * @param  string $posttype
     * @return bool
     */
    private function wantsAllPosttypes($posttype)
    {
        return empty($posttype) || $posttype == '*';
    }

    /**
     * Add an argument to the arguments list.
     * @param string    $name
     * @param mixed     $argument
     * @param bool      $multidimensional
     */
    protected function addArgument($name, $argument = null, $multidimensional = false)
    {
        if (is_array($name)) {
            return $this->addArrayOfArguments($name);
        }

        if ($multidimensional) {
            return $this->arguments = array_merge_recursive($this->arguments, [$name => $argument]);
        }

        return $this->arguments = array_merge($this->arguments, [$name => $argument]);
    }

    /**
     * Add an array of arguments to the list.
     * @param array $arguments
     * @param  bool $multidimensional
     */
    protected function addArrayOfArguments($arguments, $multidimensional = false)
    {
        foreach ($arguments as $name => $argument) {
            $this->addArgument($name, $argument, $multidimensional);
        }
    }
}
