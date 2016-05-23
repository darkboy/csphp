<?php
namespace Csp\core;
use Closure;

class CspPipeline{

    /**
     * 在 pipeline 中传递的对象
     *
     * @var mixed
     */
    protected $passable;

    /**
     * The array of class pipes.
     *
     * @var array
     */
    protected $pipes = [];

    /**
     * The method to call on each pipe.
     *
     * @var string
     */
    protected $method = 'handle';

    /**
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * Set the object being sent through the pipeline.
     *
     * @param  mixed  $passable
     * @return $this
     */
    public function send($passable) {
        $this->passable = $passable;
        return $this;
    }

    /**
     * Set the array of pipes.
     *
     * @param  dynamic|array  $pipes
     * @return $this
     */
    public function through($pipes) {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();
        return $this;
    }

    /**
     * Set the method to call on the pipes.
     *
     * @param  string  $method
     * @return $this
     */
    public function via($method) {
        $this->method = $method;
        return $this;
    }

    /**
     * Run the pipeline with a final destination callback.
     *
     * @param  \Closure  $destination
     * @return mixed
     */
    public function then(Closure $destination) {
        $firstSlice = $this->getInitialSlice($destination);
        $pipes = array_reverse($this->pipes);

        return call_user_func(
            array_reduce($pipes, $this->getSlice(), $firstSlice), $this->passable
        );
    }

    /**
     * Get the initial slice to begin the stack call.
     *
     * @param  \Closure  $destination
     * @return \Closure
     */
    protected function getInitialSlice(Closure $destination) {
        return function ($passable) use ($destination) {
            return call_user_func($destination, $passable);
        };
    }


    /**
     * Get a Closure that represents a slice of the application onion.
     *
     * @return \Closure
     */
    protected function getSlice() {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                if ($pipe instanceof Closure) {
                    return call_user_func($pipe, $passable, $stack);
                } else {

                    list($name, $parameters) = $this->parsePipeString($pipe);
                    return call_user_func_array([new $name, $this->method],
                        array_merge([$passable, $stack], $parameters));


                }
            };
        };
    }


    /**
     * Parse full pipe string to get name and parameters.
     *
     * @param  string $pipe
     * @return array
     */
    protected function parsePipeString($pipe) {
        list($name, $parameters) = array_pad(explode(':', $pipe, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }
}
