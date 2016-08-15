Workflow-bundle backported for Symfony 2.3+
===========================================
Bundle for https://github.com/symfony/workflow component backported for Symfony 2.3+ and <3.2 from Symfony 3.2's FrameworkBundle.
The main difference with original workflow management in Symfony 3.2+ applications is that 
workflow configuration must be set under `workflow` section instead of `framework` section in Symfony 3.2+.
Such difference is caused by the fact that workflow configurations are handled by 
separate WorkflowBundle introduced by this package instead of FrameworkBundle in Symfony 3.2+.

Usage
=====
Please install the bundle using composer:
```
composer require fduch/workflow-bundle
```

and register the bundle in your AppKernel class:
```php
public function registerBundles()
{
    $bundles = array(
        // ...
        new \Symfony\Bundle\WorkflowBundle\WorkflowBundle(),
    );
}
```

Please note that until Symfony 3.2 is released workflow-bundle requires "^3.2@dev" version of "symfony/workflow"
package. So in order to properly install the bundle you should set minimum stability for "symfony/workflow" 
to "@dev" by requiring "symfony/workflow: @dev" (preferred) or reduce global [minimum-stability](https://getcomposer.org/doc/04-schema.md#minimum-stability) to "dev" in your application-level composer.json. 

