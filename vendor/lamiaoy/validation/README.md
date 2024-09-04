# README #

General Validation library

### Overview and structure ###
The library provides a collection of interfaces and some default implementations for the task of validation.

**ValidationUtils** is an interface of common functions that can be shared through validating classes (typically implementing for example TypeValidation)

**ValidationDefaultValues** is an interface providding a list of default values for validation constraints so if the constraint x is not set for field y, the constraint x will be validated against default value for field y (this way only the values differing from default need to be confed).

* ValidationDefaultValuesImpl is an implementing class that takes it's values from ini file that is injected as a path to constructor of the class.

**ValidationCollection** is a collection of TypeValidations.

**TypeValidation** is a general interface for a simple validation.

* Implementing base class is AbstractTypeValidation

* * Common validation functions injected as ValidationUtils interface
* * Default values injected as ValidationDefaultValues interface

* ArrayValidation, StringValidation etc typically extend AbstractTypeValidation

* GeneralValidation is an implementation that coordinates a group of TypeValidation implementations injected to it in ValidationCollection through constructor. The TypeValidation implementation of given type is used for validating, unless not defined whereas the default type is used.

**Validation** is a general interface for taking an array of fields and validating them with constructor injected TypeValidation against the field constraint configuration also injected into constructor.

### Example usage ###

### Contribution guidelines ###

* Implementation classes must be unit tested