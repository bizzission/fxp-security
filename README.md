Sonatra Security
================

[![Latest Version](https://img.shields.io/packagist/v/sonatra/security.svg)](https://packagist.org/packages/sonatra/security)
[![Build Status](https://img.shields.io/travis/sonatra/sonatra-security/master.svg)](https://travis-ci.org/sonatra/sonatra-security)
[![Coverage Status](https://img.shields.io/coveralls/sonatra/sonatra-security/master.svg)](https://coveralls.io/r/sonatra/sonatra-security?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/sonatra/sonatra-security/master.svg)](https://scrutinizer-ci.com/g/sonatra/sonatra-security?branch=master)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/6951c069-4ec4-4cfa-a3b9-281085553fdb.svg)](https://insight.sensiolabs.com/projects/6951c069-4ec4-4cfa-a3b9-281085553fdb)

The Sonatra Security Component is a Role-Based Access Control Level 2 with advanced permissions
and sharing rules.

Features include:

- Compatible with Symfony Security and user manager library (ex. [Friends Of Symfony User Bundle](https://github.com/FriendsOfSymfony/FOSUserBundle))
- Compatible with [Doctrine extensions](https://github.com/Atlantic18/DoctrineExtensions)
- Define the roles with hierarchy in Doctrine
- Define the groups with her roles in Doctrine
- Define the user with her roles and groups in Doctrine
- Define the organization with her roles in Doctrine (optional)
- Define the organization user with her roles and groups in Doctrine (optional)
- Defined the permissions on the roles in Doctrine
- Merge the permissions of roles children of associated roles with user, role, group, organization, and token
- Security Identity Retrieval Strategy for retrieving security identities from tokens (current user,
  all roles, all groups and organization)
- AuthorizationChecker to check the permissions for domain objects
- Permission Manager retrieve and manipulate the permissions with her operations
- Permission Voter to use the Symfony Authorization Checker
- Define a role for various host with direct injection in token (regex compatible)
- Execution cache system and PSR-6 Caching Implementation for the permissions getter
- Execution cache and PSR-6 Caching Implementation for the determination of all roles in
  hierarchy (with user, group, role, organization, organization user, token)
- Share each records by user, role, groups or organization and defined her permissions
- Doctrine ORM Filter for filtering  the records in query defined by the sharing rules
- Doctrine Listener for empty the record field value for all query type
- Doctrine Listener for keep the old value in the record field value if the user has not the permission of action
- Organization with users and roles

Documentation
-------------

The bulk of the documentation is stored in the `Resources/doc/index.md`
file in this library:

[Read the Documentation](Resources/doc/index.md)

Installation
------------

All the installation instructions are located in [documentation](Resources/doc/index.md).

License
-------

This library is under the MIT license. See the complete license:

[Resources/meta/LICENSE](Resources/meta/LICENSE)

About
-----

Sonatra Security is a [sonatra](https://github.com/sonatra) initiative.
See also the list of [contributors](https://github.com/sonatra/sonatra-security/graphs/contributors).

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/sonatra/sonatra-security/issues).
