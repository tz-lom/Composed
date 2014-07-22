<?php

namespace Composed;


class DeclarationsExtractor
{
    /**
     * @var ClassInfo[]
     */
    public $classes = [];

    /**
     * @var TraitInfo[]
     */
    public $traits = [];

    /**
     * @var InterfaceInfo[]
     */
    public $interfaces = [];

    /**
     * @var FunctionInfo[]
     */
    public $functions = [];

    /**
     * @var ConstInfo[]
     */
    public $constants = [];

    protected $uses = [];

    public function __construct($statements)
    {
        $this->traverseNamespace($statements, '');
    }

    protected function traverseNamespace($statements, $ns)
    {
        foreach($statements as $stmt)
        {
            if($stmt instanceof \PHPParser_Node_Stmt_Class)
            {
                $this->traverseClass($stmt, $ns);
            }
            if($stmt instanceof \PHPParser_Node_Stmt_Namespace)
            {
                $this->traverseNamespace($stmt->stmts, $stmt->name.'\\');
            }
            if($stmt instanceof \PHPParser_Node_Stmt_Interface)
            {
                $this->traverseInterface($stmt, $ns);
            }
            if($stmt instanceof \PHPParser_Node_Stmt_Function)
            {
                $this->traverseFunction($stmt, $ns);
            }
            if($stmt instanceof \PHPParser_Node_Stmt_Const)
            {
                $this->traverseConstant($stmt, $ns);
            }
            if($stmt instanceof \PHPParser_Node_Stmt_Trait)
            {
                $this->traverseTrait($stmt, $ns);
            }
            if($stmt instanceof \PHPParser_Node_Stmt_Use)
            {
                /**
                 * @var \PHPParser_Node_Stmt_Use $stmt
                 */
                foreach($stmt->uses as $u)
                {
                    $this->uses[$u->name->getLast()] = $u->name->toString();
                }
            }
        }
    }

    protected function traverseClass(\PHPParser_Node_Stmt_Class $class, $ns)
    {
        $implements = [];
        foreach($class->implements as $imp)
        {
            $implements[] = $this->replaceUses($imp->toString());
        }

        $methods = [];
        $constants = [];

        foreach($class->stmts as $stmt)
        {
            if($stmt instanceof \PHPParser_Node_Stmt_ClassMethod)
            {
                /**
                 * @var \PHPParser_Node_Stmt_ClassMethod $stmt
                 */
                if($stmt->isPublic() && !$stmt->isAbstract())
                {
                    $methods[] = new MethodInfo($stmt->name, $this->extractArguments($stmt->params), $stmt->isStatic());
                }
            }
            if($stmt instanceof \PHPParser_Node_Stmt_ClassConst)
            {
                /**
                 * @var \PHPParser_Node_Stmt_ClassConst $stmt
                 */
                foreach($stmt->consts as $c)
                {
                    $constants[] = new ConstInfo($c->name);
                }
            }
        }

        $this->classes[] = new ClassInfo(
            $ns.$class->name,
            $this->replaceUses($class->extends),
            $implements,
            $methods,
            $constants,
            $class->type,
            $class->type
        );
    }

    protected function traverseInterface(\PHPParser_Node_Stmt_Interface $interface, $ns)
    {
        //@todo
    }

    protected function traverseFunction(\PHPParser_Node_Stmt_Function $function, $ns)
    {
        $this->functions[] = new FunctionInfo($ns.$function->name, extractArguments($function->params));
    }

    protected function extractArguments($args)
    {
        $ret = [];
        foreach($args as $arg)
        {
            /**
             * @var \PHPParser_Node_Param $arg
             */
            if($arg->type instanceof \PHPParser_Node_Name)
            {
                $type = $arg->type->toString();
            }
            else
            {
                $type = $arg->type;
            }

            $ret[] = new ArgumentInfo($this->replaceUses($arg->name), $type, is_null($arg->default));
        }
        return $ret;
    }

    protected function traverseConstant(\PHPParser_Node_Stmt_Const $const, $ns)
    {
        foreach($const->consts as $c)
        {
            $this->constants[] = new ConstInfo($ns.$c->name);
        }
    }

    protected function traverseTrait(\PHPParser_Node_Stmt_Trait $trait, $ns)
    {
        //@todo
    }

    protected function replaceUses($name)
    {
        $name = (string) $name;
        if(array_key_exists($name, $this->uses))
        {
            return $this->uses[$name];
        }
        else
        {
            return $name;
        }
    }


} 