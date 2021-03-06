<a href="https://travis-ci.org/ordermind/logical-permissions-php" target="_blank"><img src="https://travis-ci.org/ordermind/logical-permissions-php.svg?branch=2.x" /></a>
# logical-permissions

This is a generic library that provides support for array-based permissions with logic gates such as AND and OR. You can register any kind of permission types such as roles and flags. The idea with this library is to be an ultra-flexible foundation that can be used by any framework. Supported PHP version is 5.6 or higher.

## Getting started

### Installation

`composer require ordermind/logical-permissions`

### Usage

#### Register permission types

Permission types are used to check different kinds of conditions for access control, and the first thing to do is to create one of these and register it. Let's say, for example, that we want to determine access using the current user's roles. First you create a class that implements ```Ordermind\LogicalPermissions\PermissionTypeInterface``` like this:

```php
use Ordermind\LogicalPermissions\PermissionTypeInterface;

class MyPermissionType implements PermissionTypeInterface {
  public static function getName() {
    return 'role';
  }

  public function checkPermission($role, $context) {
    $access = FALSE;
    if(!empty($context['user']['roles'])) {
      $access = in_array($role, $context['user']['roles']);
    }

    return $access;
  }
}
```
Now we have implemented the two required methods - getName() and checkPermission() - and created a simple example for checking a role for a user. The name of the permission type is going to be used later as a key in your permission tree, and the checkPermission() method is where you, in this case, check whether the current user has a role or not.

Once you have created a permission type you can register it like this:

```php
use Ordermind\LogicalPermissions\AccessChecker;

$permissionType = new MyPermissionType();
$accessChecker = new AccessChecker();
$permissionTypeCollection = $accessChecker->getPermissionTypeCollection();
$permissionTypeCollection->add($permissionType);
```
#### Check access

Now everything is set and you can check the access for a user based on their roles:
```php
$permissions = [
  'role' => 'admin', // The key 'role' here is the name of your permission type
];
$user = ['roles' => ['admin', 'sales']];
$access = $accessChecker->checkAccess($permissions, ['user' => $user]);
// TRUE
```

### Permission trees
In the previous example, we had a variable called ```$permissions``` that looked like this:
```php
$permissions = [
  'role' => 'admin',
];
```
This is an example of a **permission tree**. A permission tree is a hierarchical combination of permissions that is evaluated in order to determine access for a specific action. Let's say for example that you want to restrict access for updating a user. You'd like only users with the role "admin" to be able to update any user, but users should also be able to update their own user data (or at least some of it). With the format that this library provides, these conditions could be expressed elegantly in a permission tree as such:

```php
[
  'OR' => [
    'role' => 'admin',
    'flag' => 'is_author',
  ],
]
```

In this example `role` and `flag` are the evaluated permission types. For this example to work you will need to register the permission types 'role' and 'flag' according to the previous guide.

### Bypassing access checks
This library also supports rules for bypassing access checks completely for superusers. In order to use this functionality you first need to create a class that implements ```Ordermind\LogicalPermissions\BypassAccessCheckerInterface``` like this:

```php
use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;

class MyBypassAccessChecker implements BypassAccessCheckerInterface {
  public function checkBypassAccess($context) {
    $bypassAccess = FALSE;
    if($context['user']['id'] == 1) {
      $bypassAccess = TRUE;
    }

    return $bypassAccess;
  }
}
```
Then you can register it like this:
```php
$bypassAccessChecker = new MyBypassAccessChecker();
$accessChecker->setBypassAccessChecker($bypassAccessChecker);
```
From now on, every time you call ```$accessChecker->checkAccess()``` the user with the id 1 will be exempted so that no matter what the permissions are, they will always be granted access. If you want to make exceptions, you can do so by adding `'NO_BYPASS' => TRUE` to the first level of a permission tree. You can even use permissions as conditions for `NO_BYPASS`.

Examples:

```php
//Disallow access bypassing
[
  'NO_BYPASS' => TRUE,
  'role' => 'editor',
]
```

```php
//Disallow access bypassing only if the user is an admin
[
  'NO_BYPASS' => [
    'role' => 'admin',
  ],
  'role' => 'editor',
]
```

## Logic gates

Currently supported logic gates are [AND](#and), [NAND](#nand), [OR](#or), [NOR](#nor), [XOR](#xor) and [NOT](#not). You can put logic gates anywhere in a permission tree and nest them to your heart's content. All logic gates support only an array as their value, except the NOT gate which has special rules. If an array of values does not have a logic gate as its key, an OR gate will be assumed.

### AND

A logic AND gate returns true if all of its children return true. Otherwise it returns false.

Examples:

```php
//Allow access only if the user is both an editor and a sales person
[
  'role' => [
    'AND' => ['editor', 'sales'],
  ],
]
```

```php
//Allow access if the user is both a sales person and the author of the document
[
  'AND' => [
    'role' => 'sales',
    'flag' => 'is_author',
  ],
]
```

### NAND

A logic NAND gate returns true if one or more of its children returns false. Otherwise it returns false.

Examples:

```php
//Allow access by anyone except if the user is both an editor and a sales person
[
  'role' => [
    'NAND' => ['editor', 'sales'],
  ],
]
```

```php
//Allow access by anyone, but not if the user is both a sales person and the author of the document.
[
  'NAND' => [
    'role' => 'sales',
    'flag' => 'is_author',
  ],
]
```

### OR

A logic OR gate returns true if one or more of its children returns true. Otherwise it returns false.

Examples:

```php
//Allow access if the user is either an editor or a sales person, or both.
[
  'role' => [
    'OR' => ['editor', 'sales'],
  ],
]
```

```php
//Allow access if the user is either a sales person or the author of the document, or both
[
  'OR' => [
    'role' => 'sales',
    'flag' => 'is_author',
  ],
]
```

### Shorthand OR

As previously mentioned, any array of values that doesn't have a logic gate as its key is interpreted as belonging to an OR gate.

In other words, this permission tree:

```php
[
  'role' => ['editor', 'sales'],
]
```
is interpreted exactly the same way as this permission tree:
```php
[
  'role' => [
    'OR' => ['editor', 'sales'],
  ],
]
```


### NOR

A logic NOR gate returns true if all of its children returns false. Otherwise it returns false.

Examples:

```php
//Allow access if the user is neither an editor nor a sales person
[
  'role' => [
    'NOR' => ['editor', 'sales'],
  ],
]
```

```php
//Allow neither sales people nor the author of the document to access it
[
  'NOR' => [
    'role' => 'sales',
    'flag' => 'is_author',
  ],
]
```


### XOR

A logic XOR gate returns true if one or more of its children returns true and one or more of its children returns false. Otherwise it returns false. An XOR gate requires a minimum of two elements in its value array.

Examples:

```php
//Allow access if the user is either an editor or a sales person, but not both
[
  'role' => [
    'XOR' => ['editor', 'sales'],
  ],
]
```

```php
//Allow either sales people or the author of the document to access it, but not if the user is both a sales person and the author
[
  'XOR' => [
    'role' => 'sales',
    'flag' => 'is_author',
  ],
]
```

### NOT

A logic NOT gate returns true if its child returns false, and vice versa. The NOT gate is special in that it supports either a string or an array with a single element as its value.

Examples:

```php
//Allow access for anyone except editors
[
  'role' => [
    'NOT' => 'editor',
  ],
]
```

```php
//Allow access for anyone except the author of the document
[
  'NOT' => [
    'flag' => 'is_author',
  ],
]
```

## Boolean Permissions

Boolean permissions are a special kind of permission. They can be used for allowing or disallowing access for everyone (except those with bypass access). They are not allowed as descendants to a permission type and they may not contain children. Both true booleans and booleans represented as uppercase strings are supported. Of course a simpler way to allow access to everyone is to not define any permissions at all for that action, but it might be nice sometimes to explicitly allow access for everyone.

Examples:

```php
//Allow access for anyone
[
  TRUE,
]

//Using a boolean without an array is also permitted
TRUE
```

```php
//Example with string representation
[
  'TRUE',
]

//Using a string representation without an array is also permitted
'TRUE'
```

```php
//Deny access for everyone except those with bypass access
[
  FALSE,
]

//Using a boolean without an array is also permitted
FALSE
```

```php
//Example with string representation
[
  'FALSE',
]

//Using a string representation without an array is also permitted
'FALSE'
```

```php
//Deny access for everyone including those with bypass access
[
  FALSE,
  'NO_BYPASS' => TRUE,
]
```


# API Documentation

## Table of Contents

* [AccessChecker](#accesschecker)
    * [setPermissionTypeCollection](#setpermissiontypecollection)
    * [getPermissionTypeCollection](#getpermissiontypecollection)
    * [setBypassAccessChecker](#setbypassaccesschecker)
    * [getBypassAccessChecker](#getbypassaccesschecker)
    * [getValidPermissionKeys](#getvalidpermissionkeys)
    * [checkAccess](#checkaccess)
* [PermissionTypeCollection](#permissiontypecollection)
    * [add](#add)
    * [remove](#remove)
    * [has](#has)
    * [get](#get)
    * [toArray](#toarray)

## AccessChecker

Checks access based on registered permission types, a permission tree and a context.



* Full name: \Ordermind\LogicalPermissions\AccessChecker
* This class implements: \Ordermind\LogicalPermissions\AccessCheckerInterface


### setPermissionTypeCollection

Sets the permission type collection.

```php
AccessChecker::setPermissionTypeCollection( \Ordermind\LogicalPermissions\PermissionTypeCollectionInterface $permissionTypeCollection ): \Ordermind\LogicalPermissions\AccessCheckerInterface
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$permissionTypeCollection` | **\Ordermind\LogicalPermissions\PermissionTypeCollectionInterface** |  |




---

### getPermissionTypeCollection

Gets the permission type collection.

```php
AccessChecker::getPermissionTypeCollection(  ): \Ordermind\LogicalPermissions\PermissionTypeCollectionInterface|NULL
```







---

### setBypassAccessChecker

Sets the bypass access checker.

```php
AccessChecker::setBypassAccessChecker( \Ordermind\LogicalPermissions\BypassAccessCheckerInterface $bypassAccessChecker ): \Ordermind\LogicalPermissions\AccessCheckerInterface
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bypassAccessChecker` | **\Ordermind\LogicalPermissions\BypassAccessCheckerInterface** |  |




---

### getBypassAccessChecker

Gets the bypass access checker.

```php
AccessChecker::getBypassAccessChecker(  ): \Ordermind\LogicalPermissions\BypassAccessCheckerInterface|NULL
```







---

### getValidPermissionKeys

Gets all keys that can be used in a permission tree.

```php
AccessChecker::getValidPermissionKeys(  ): array
```





**Return Value:**

Valid permission keys.



---

### checkAccess

Checks access for a permission tree.

```php
AccessChecker::checkAccess( array|string|boolean $permissions, array|object|NULL $context = NULL, boolean $allowBypass = TRUE ): boolean
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$permissions` | **array&#124;string&#124;boolean** | The permission tree to be evaluated. |
| `$context` | **array&#124;object&#124;NULL** | (optional) A context that could for example contain the evaluated user and document. Default value is NULL. |
| `$allowBypass` | **boolean** | (optional) Determines whether bypassing access should be allowed. Default value is TRUE. |


**Return Value:**

TRUE if access is granted or FALSE if access is denied.



---

## PermissionTypeCollection

Collection of permission types.



* Full name: \Ordermind\LogicalPermissions\PermissionTypeCollection
* This class implements: \Ordermind\LogicalPermissions\PermissionTypeCollectionInterface


### add

Adds a permission type to the collection.

```php
PermissionTypeCollection::add( \Ordermind\LogicalPermissions\PermissionTypeInterface $permissionType, boolean $overwriteIfExists = FALSE ): \Ordermind\LogicalPermissions\PermissionTypeCollectionInterface
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$permissionType` | **\Ordermind\LogicalPermissions\PermissionTypeInterface** |  |
| `$overwriteIfExists` | **boolean** | (optional) If the permission type already exists in the collection, it will be overwritten if this parameter is set to TRUE. If it is set to FALSE, Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyExistsException will be thrown. Default value is FALSE. |




---

### remove

Removes a permission type by name from the collection. If the permission type cannot be found in the collection, nothing happens.

```php
PermissionTypeCollection::remove( string $name ): \Ordermind\LogicalPermissions\PermissionTypeCollectionInterface
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** | The name of the permission type. |




---

### has

Checks if a permission type exists in the collection.

```php
PermissionTypeCollection::has( string $name ): boolean
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** | The name of the permission type. |




---

### get

Gets a permission type by name. If the permission type cannot be found, NULL is returned.

```php
PermissionTypeCollection::get( string $name ): \Ordermind\LogicalPermissions\PermissionTypeInterface|NULL
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** | The name of the permission type. |




---

### toArray

Returns a PHP array representation of this collection.

```php
PermissionTypeCollection::toArray(  ): array
```







---

