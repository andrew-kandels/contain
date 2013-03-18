Zend Framework 2 Stuctured Data Entities
========================================

Contain is a Zend Framework 2 library allowing for passing structured data objects 
with strict types and internal validation throughout your application and persistence 
layer. It integrates nicely with Zend Framework 2 components like `Zend\Form`, `Zend\Validator`, 
`Zend\InputFilter` and events via `Zend\EventManager`.

Why?
----

Working with PHP, you'll often find yourself passing around arrays to communicate data:

    // controller, form $_POST, etc.
    $data = array(
        'firstName' => 'Andrew', 
        'lastName' => 'Kandels
    );
    $service->save($model);

In this case, `$service` might represent an object relational mapper (ORM), a database, a web service, 
or so forth. Who's responsible for filtering and validating the data? The controller might check that 
a value was passed while the form might filter and validate it. The service or data layer likely validates 
it again; adding additional work and fragmenting the logic into different areas making it hard to maintain 
and keep consistent should you need to change it.

PHP's Loose Typing
------------------

Scalar types can't be specified in typing arguments of functions and methods in PHP (nor should this exist in my opinion). The loose typing of the language is quite nice in most respects, except for when you're dealing with data models like a user profile record.

If you're passing around an array representing a user, it's quite important the user's first name is stored as a string and meets certain expectations. Every layer of your application from the form display to the controller to the persistence into your database rely on this; and unfortunately, probably do some repetitive validation.

Contain's Entities
------------------
Contain refers to structured data objects, or data models, as entities. They are smart objects that replace arrays for storing structured data and should be passed throughout your application anytime you refer to any part, or all of, the data they contain. Here's an entity definition class:

    class User extends Contain\Entity\Definition\AbstractDefinition
    {
        public function setUp()
        {
            $this->registerTarget(AbstractDefinition::FILTER, __DIR__ . '/../Filter')
                 ->registerTarget(AbstractDefinition::FORM, __DIR__ . '/../Form')
                 ->registerTarget(AbstractDefinition::ENTITY, __DIR__ . '/..');
     
            $this->setProperty('firstName', 'string', array(
                'required' => true,
                'type' => '<code>Zend\Form\Element\Text',</code>
                'attributes' => array(
                    'id' => 'firstName',
                ),
                'validators' => array(
                    array('name' => 'StringLength', 'options' => array(
                        'min' => 1,
                        'max' => 60,
                    )),
                ),
                'filters' => array(
                    array('name' => 'StringTrim'),
                ),
                'options' => array(
                    'label' => 'Name',
                ),
            ));
        }
    }

The definition class above is run through Contain's compiler which creates three classes we can use within our application: an entity class and two scaffolding classes that extend `Zend\InputFilter` and `Zend\Form`.

The Entity Class
----------------
The user entity stores your properties, like firstName. It's extremely lightweight and is really just a better way to pass around a representation of a user within your application:

    $user = new User(array('firstName' => 'Andrew'));
    print_r($user->export());   // array('firstName' => 'Andrew')
    echo $user->getFirstName(); // Andrew

Because the entity is compiled, methods like getFirstName can exist in the class for each of your properties, so your entities will work with code completion if you develop using an IDE and we don't have to rely on magic methods like __call so the entities are lean and fast.

Because you specified the entity's firstName property as a string, trying to set a non-string value will be auto-converted or generate an exception:

    $user->setFirstName(123); // saved as '123'
    $user->setFirstName(array('random')); // throws an exception

For this reason, passing entities throughout the different layers of your application reduces the amount of validation needed at each stage. When dealing with data, we can now rewrite the following:

    // old way
    function saveUser($firstName) {
        if (!is_string($firstName)) { // likely duplicate validation
            return false;
        }
     
        $this->db->insert(array('firstName' => $firstName));
    }

When passing data between layers of your application, pass the entities instead of arrays or ordered lists of arguments:

    // better way
    function saveUser(User $user) {
        $this->db->insert($user->export());
    }

Partial Population
------------------
Entities can be partially populated. Entities know the difference between an empty value and an unset value:

    $user = new User();
    print_r($user); // array()
     
    $user = new User(array('firstName' => ''));
    print_r($user); // array('firstName' => '')

Data Types
----------
Entities support a number of data types (string, integer, etc.) and you can create custom data types. Data types can also be pointers to other entities or lists of entities. Nesting works great for representing MongoDB documents:

    $user = new User(array(
        'firstName' => 'Andrew',
        'email' => new Email(array(
            'address' => 'akandels@gmail.com',
        )),
    ));
    print_r($user->export()); 
    /* 
        array(
            'firstName' => 'Andrew', 
            'email' => array(
                'address' => 'akandels@gmail.com'
            )
        )
    */

Import / Export (json, xml, etc.)
---------------------------------
Interally, certain properties like child entities, dates, etc. are stored as objects; however, as a rule, all entity types must return non-object, plain-type (i.e.: non-object) values when export() is invoked. For this reason, export() can always safely be converted into json, xml, etc. and is safe to be serialized. Passing the response of export() to a new entity is guaranteed to create an exact copy of the original entity.

Zend Framework 2: Filtering, Validation
---------------------------------------
The third argument in the definition class configuration matches what you'd pass to different areas within the Zend Framework. The validators and filters indexes match what you'd pass to Zend\InputFilter. The attributes, options and type indexes match what you'd pass to a form element in Zend\Form. Contain takes these indexes and compiles them into classes for quick scaffolding.

You likely won't use the classes as-is because your form probably contains other elements: CSRF, submit buttons, etc.; but the form and filters you do build can pull the elements from the scaffolding and consolidate your filtering and validation logic to one place.

    // in your <code>Zend\Form:</code>
    $user = new Namespace\Entity\Form\User();
    $this->add($user->get('firstName'));
     
    // in your <code>Zend\InputFilter:</code>
    $user = new Namespace\Entity\Filter\User();
    $this->add($user->get('firstName'));

Let's say you have an ajax call to check if the data a user typed into a form was valid. It's quite easy to quickly filter and validate a single property:

    // in your controller action:
    $user = new User($request->getPost()); // non-defined properties will be ignored
    return array(
        'isValid'  => $user->isValid('firstName'), // false
        'messages' => $user->messages()            // array('firstName' => array('error1', ...))
    );

Now all your validation and filtering logic is controller from one place. When it changes, just update your definition file and recompile.

Events
------
Contain entities have their own Zend\EventManager (or they can use someone else's, like a data mapper's). They fire events when things happen internally. Trap events like property.get to override default behavior:

    $user->getEventManager()->attach('property.get', function($e) {
        $prop = $e->getParam('property');
        if ( /* some check */ ) {
            return 'new value';
        }
    });

Extending Entities with Custom Methods
--------------------------------------
You can add additional methods as a convenience to your definition class and the Contain compiler will use reflection to add them to your compiled entity class:

    // in the Definition\User.php class:
    public function setUp() {
        $this->registerMethod('getFullName');
    }
     
    public function getFullName() {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

Mixins
------
Pull in as many other entity definition classes as you'd like, Contain considers them mixins. All properties, methods, events and otherwise will be compiled into your output classes. Contain includes some standard mixins like Timestampable that add createdAt and updatedAt properties and attach events to update them:

    // in the Definition\User.php:
    public function setUp() {
        $this->registerMixin('Contain\Entity\Definition\Timestampable');
    }
    ...
    print_r($user->export()); // array('createdAt' => '2012-01-01 00:00:00', ...)

It's not another ORM
--------------------
Structured data entities don't have to represent rows of data from a database. They can be used to create object representations of data anywhere within your application where type checking, validation or filtering is important and often repeated. Contain doesn't require a database, nor is it an ORM.

But...
------
A side project contain-mapper can optionally be installed to include mappers to data stores and databases like MongoDB, Memcached, the file system, and others to come; but, the mapper is optional -- you could easily use Contain entities using Zend\Db, Doctrine, or something else. Here's an example of using the mapper with MongoDB:

    $mongo = new Mongo('mongodb://localhost');
    $conn = new ContainMapper\Driver\MongoDB\Connection($mongo, 'database', 'collection');
    $mapper = new ContainMapper\Driver\MongoDB\Driver($conn);
    if (!$user = $mapper->findOne(array('firstName' => 'Andrew'))) {
        $user = new User(array('firstName' => 'Andrew'));
        $mapper->persist($user);
        echo $user->getExtendedProperty('_id'); // really long hash
    }

Extended Properties
-------------------
You can store additional undefined properties as hidden, extended properties. These won't be included in things like export() but it's a handy place to store hidden or internal values. The above MongoDB mapper uses them to store MongoDB's special _id property:

    $this->mapper->persist($user);
    $user->getExtendedProperty('_id'); // long hash
    You can also define a property as primary which will use the value of their property as the special _id value. The MongoDB driver in generally is smart, and runs efficient queries that only update changed properties using $set. It also supports other features like $inc, pushing and pulling from lists, etc..
    If you want to switch out MongoDB to Memcached to implement a nice caching layer, it's as easy as switching the connection:
    $mongo = new Mongo('mongodb://localhost');
    $conn = new ContainMapper\Driver\MongoDB\Connection($mongo, 'database', 'collection');
    $mapper = new ContainMapper\Driver\MongoDB\Driver($conn);
    $mapper->persist($user); // stored in MongoDB
     
    $conn = new ContainMapper\Driver\Memcached\Connection(new Memcached());
    $mapper->setConnection($conn);
    $mapper->persist($user); // now stored in memcached

Installation
------------
The Contain library and the optional mapper are both available on GitHub. Both projects are on Packagist, so if you're using Composer, just add them to your project's composer.json:

    "require": {
        "akandels/contain": "@dev",
        "akandels/contain-mapper": "@dev",
        ...
    }

Summary / Feedback
------------------
There's a lot more I could talk about; but I wanted to cover the basics (more to follow). I wrote Contain primary for MongoDB on the Zend Framework 2, so there's some heavy influence there. The project has been under development on and off for the past several months (since Zend Framework 2's earlier beta releases). It does include a full unit test suite and will continue to be under active development.

If you have any feedback, please send it my way. I'm @andrewkandels on Twitter or akandels [at] gmail.com. If you have any ideas for improvements, feel free to send me some pull requests. If you're using it, let me know! Enjoy.
