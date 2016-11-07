Sonatra Security
================

[![Latest Version](https://img.shields.io/packagist/v/sonatra/security.svg)](https://packagist.org/packages/sonatra/security)
[![Build Status](https://img.shields.io/travis/sonatra/sonatra-security/master.svg)](https://travis-ci.org/sonatra/sonatra-security)
[![Coverage Status](https://img.shields.io/coveralls/sonatra/sonatra-security/master.svg)](https://coveralls.io/r/sonatra/sonatra-security?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/sonatra/sonatra-security/master.svg)](https://scrutinizer-ci.com/g/sonatra/sonatra-security?branch=master)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/74707490-7a7f-4dd8-91c9-84af5de547a1.svg)](https://insight.sensiolabs.com/projects/74707490-7a7f-4dd8-91c9-84af5de547a1)

The Sonatra Security implements all functionnalities of 
[Symfony Advanced ACL Concepts](http://symfony.com/doc/current/cookbook/security/acl_advanced.html)
and adds some interesting features.


Features include:

- Ability to define permissions for Entity (Class, Class Field, Record, Record Field)
- Service manipulator (helper) for ACL/ACE manipulation (read, grant, revoke permissions)
- Service manager (helper) for check granting on domain object (granted, field granted, preload ACLs)
- ACL Rule Definition for optimize the ACL queries (and ability to create a sharing rule)
- ACL Rule Filter Definition for filter the records in query
- ACL Voter for use the Symfony Authorization Checker
- Ability to set permissions on roles or users, but also directly on groups
- Ability to define a hierarchy of role (with all roles in all associated groups)
- Merge the permissions of roles children of associated roles with user, role, group, and token
- Define a role for various host with direct injection in token (regex compatible)
- Execution cache system for the ACL/ACE getter
- Execution cache and PSR-6 Caching Implementation for the determination of all roles in hierarchy (with user, group, role, token)
- Doctrine ORM Filter for filter the records in query (using ACL Rule Filter Definition)
- Doctrine Listener for empty the record field value for all query type
- Doctrine Listener for keep the old value in the record field value if the user has not the permission of action
- Ability to define a role for a hostname (defined with regex)

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
