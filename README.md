Workflow-bundle backported for Symfony 2.3+
===========================================

[![Build Status](https://travis-ci.org/fduch/workflow-bundle.svg?branch=master)](https://travis-ci.org/fduch/workflow-bundle)

Bundle for https://github.com/symfony/workflow component backported for Symfony 2.3+ and <3.2 from Symfony 3.2's FrameworkBundle.
The main difference with original workflow management in Symfony 3.2+ applications is that 
workflow configuration must be set under `workflow` section instead of `framework` section in Symfony 3.2+.
Such difference is caused by the fact that workflow configurations are handled by 
separate WorkflowBundle introduced by this package instead of FrameworkBundle in Symfony 3.2+.


> This version of the Bundle uses stable 3.2+ version of the [symfony/workflow](https://github.com/symfony/workflow) component.
> Due to BC-breaks introduced in Workflow component and FrameworkBundle inside 3.2-branch (https://github.com/symfony/symfony/pull/20462)
> please use ([1.x](https://github.com/fduch/workflow-bundle/tree/1.x)) branch of the fduch/workflow-bundle with [symfony/workflow](https://github.com/symfony/workflow) component in old versions up to [cdddaeec794e4096f2f80f0298fc1a4b5bfacb83](https://github.com/symfony/workflow/commit/cdddaeec794e4096f2f80f0298fc1a4b5bfacb83) (non-including).
> Unfortunately there is no way to define such version constraint restrictions on the composer.json level: it can be done nether with `require` nor  `conflict` sections, so you should check it manually.


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

You can also check project [fduch/symfony-standard-workflow](https://github.com/fduch/symfony-standard-workflow) to see application example based on Symfony Standard Edition with workflow configured using `fduch/workflow-bundle`

Please note that until Symfony 3.2 is released workflow-bundle requires "^3.2@dev" version of "symfony/workflow"
package. So in order to properly install the bundle you should set minimum stability for "symfony/workflow" 
to "@dev" by requiring "symfony/workflow: @dev" (preferred) or reduce global [minimum-stability](https://getcomposer.org/doc/04-schema.md#minimum-stability) to "dev" in your application-level composer.json. 

