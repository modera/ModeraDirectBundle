# ModeraDirectBundle

ModeraDirectBundle is an implementation of ExtDirect specification to Symfony framework.

## Installation

### Step 1: Download the Bundle

``` bash
composer require modera/direct-bundle:5.x-dev
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

### Step 2: Enable the Bundle

This bundle should be automatically enabled by [Flex](https://symfony.com/doc/current/setup/flex.html).
In case you don't use Flex, you'll need to manually enable the bundle by
adding the following line in the `config/bundles.php` file of your project:

``` php
<?php
// config/bundles.php

return [
    // ...
    Modera\DirectBundle\ModeraDirectBundle::class => ['all' => true],
];
```

### Step 3: Add routing

``` yaml
// config/routes.yaml

direct:
    resource: "@ModeraDirectBundle/Resources/config/routing.yml"
```

## How to use

### Add the ExtDirect API into your page

If you is using Twig engine, only add the follow line in your views page at the
script section:

``` html
<script type="text/javascript" src="{{ url('api') }}"></script>
```

Or if you are not using a template engine:

``` html
<script type="text/javascript" src="/api.js"></script>
```

### Expose your controller methods to ExtDirect Api

``` php
// .../Acme/DemoBundle/Controller/ExampleController.php

namespace Acme\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Modera\DirectBundle\Annotation\Remote;
use Modera\DirectBundle\Annotation\Form;

class ExampleController extends AbstractController
{
   /**
    * Single exposed method.
    *
    * @Remote    // this annotation expose the method to API
    *
    * @param  array $params
    * @return string
    */
    public function indexAction(array $params)
    {
        return 'Hello ' . $params['name'];
    }

    /**
     * An action to handle forms.
     *
     * @Remote   // this annotation expose the method to API
     * @Form     // this annotation expose the method to API with formHandler option
     *
     * @param array $params Form submitted values
     * @param array $files  Uploaded files like $_FILES
     */
    public function testFormAction(array $params, array $files)
    {
        // your proccessing
        return true;
    }
}
```

### Call the exposed methods from JavaScript

``` js
// 'AcmeDemo' is the Bundle name without 'Bundle'
// 'Example' is the Controller name without 'Controller'
// 'index' is the method name without 'Action'
Actions.AcmeDemo_Example.index({ name: 'ExtDirect' }, function(r) {
   alert(r);
});
```

## Licensing

This bundle is under the MIT license. See the complete license in the bundle:
Resources/meta/LICENSE
