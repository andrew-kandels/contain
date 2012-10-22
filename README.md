Contain
=======

Working with PHP, you'll often find yourself passing around arrays to communicate data:

// in controller:
$model = array('firstName' => 'Andrew', 'lastName' => 'Kandels);
$this->mapper->save($model);

The benefit is we can pass whatever we like. Backwards compatibility is easy to maintain, the recipient can use whatever indexes it needs. The downside is there's nothing to enforce the model you pass the mapper contains a first name element. We can get around that by changing our save method a bit:

// in mapper:
function save($firstName, $lastName)

Now we can be sure we'll receive a first and last name and can skip those ugly isset() checks. The downside is the client has to know which order to pass the arguments. It might not be tedious with just a first and last name; but once our model evolves a bit it will get difficult to maintain. If we change the arguments or their order, we'll need to update every caller.

There's also nothing to verify our first name is a string. This often introduces a habbit of over-validating arguments each time you pass the data between areas of your application. Where's the right place to validate that first name exists and is a string? The controller? The mapper? A service layer?

What level of validation is each layer responsible for? One might need to know if it's a string while another might care if it's greater than 30 characters or already exists in a database. Generally this logic is stored in different places which is difficult to maintain and often repetitive.

By using Contain, you create definition classes which define data entities Entities are basically just glorified arrays you pass around your application. You might use them to represent a model, or just about anything else. The definition defines the properties of the entity. Each property has an enforced type (string, sub-entity, etc.) and all kinds of other information centralized in one place.

Instead of passing an array, create classes with methods that accept and return entities:

// in a controller:
$user = $this->mapper->save(new User(array('firstName' => 'Andrew', 'lastName' => 'Kandels')));
echo $user->getFirstName(); // Andrew

The entity will enforce a firstName element exists and is a string. If you try and set it to an array, you'll get an exception thrown. If you pass it an integer, it will convert it.

Contain uses a compiler to auto-generate entity classes. In this sense, there are no "magic" methods so you won't incur the performance hit. You'll also be able to tab complete methods like "setFirstName".

Contain integrates with Zend Framework 2. In addition to building the entity class itself, it can also create forms, and input filters (with their validators and filters). Use them as-is for quick scaffolding or pull individual elements to consolidate the business logic into one place within your application.

Entities shouldn't be complicated or contain nested trees of objects that are impossible to inpect or serialize. Contain entities can traverse between arrays and entity objects easily:

$arr = $entity->export(); // array('firstName' => 'Andrew')
$entity->fromArray($arr); // back to an entity

All property types in Contain export themselves to plain PHP types (arrays, strings, etc.) so serialization is always possible and pain-free.

My intent is not to make Contain yet another object relational mapper (ORM). Contain is a library for passing around data within your application and centralizing the logic around that data. How you store it or use it is up to you an your application.
