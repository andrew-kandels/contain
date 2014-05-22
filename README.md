Contain PHP Entity Models
=========================

Compiled lightweight entity models for PHP. Type validation, data encapsulation, filtering and validation without the ORM.

Entity Models
-------------
An entity model is a container for one or more pieces of data that describe something
in your application. For example, a __User__ model might be described as:

    $user = array(
        'firstName' => 'Andrew',
        'lastName'  => 'Kandels',
    );

Say you develop a service to create a user. There are two common ways to pass your
model to that service.

Passing Arrays
--------------
    class Service {
        public function addUser(array $data) {
            ...
        }
    }

    $service->addUser($user);

The problem with this approach is that the __addUser__ method is only guaranteed to
receive an array of data. It could be passed an empty array, an array with typos for
its data (firstName -> first_name) or possibly even non-string values for its first
or last name indexes.

Placeholder Parameters
----------------------
    class Service {
        public function addUser($firstName, $lastName) {
            ...
        }
    }

    $service->addUser('Andrew', 'Kandels');

The first problem with this approach is when you start creating larger models the
exact position of each parameter becomes difficult to remember. PHP also doesn't
supported named parameters like other languages. Removing parameters later could also
break backwards compatibility.

PHP can't do type-checking on scalar values like strings either, so the service is
still responsible for validating that __$firstName__ is a string and not an object or
something unusable.

Meet Contain
============
Contain aims to solve this problem by creating lightweight entity models that you pass
between services, controllers, views and just about anywhere else in your application:

    class Service {
        public function addUser(User $user) {
            ...
        }
    }

    $user = new User(array(
        'firstName' => 'Andrew',
        'lastName' => 'Kandels'
    ));

    $service->addUser($user);

Contain takes care of these things for you with the entity model:

* It encapsulates properties into a simple object (no isset() checks)
* It creates simple setters and getters
* It validates the types of properties
    * Strings are strings
    * Numbers are numbers
* It converts properties to expected types
    * "2014-01-01" -> new DateTime(strtotime('2014-01-01'))

View the complete [Project Documentation](http://www.contain-php.org) at http://www.contain-php.org.
