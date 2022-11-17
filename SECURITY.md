# Security Policy

Goals of the Milex Security Team
---------------------------------

*   Resolve reported security issues in a Security Advisory
*   Provide documentation on how to write secure code
*   Provide documentation on securing your Milex instance
*   Help the infrastructure team to keep the \*.milex.org infrastructure secure

Scope of the Milex Security Team
---------------------------------

The Milex Security Team operates with a limited scope and only directly responds to issues with Milex core, officially supported plugins and the \*.milex.org network of websites. The team does not directly handle potential vulnerabilities with third party plugins or individual Milex instances.

Which Releases Get Security Advisories?
---------------------------------------

Milex 3 will receive security advisories until 15 December 2021.

Starting with the release of Milex 3.0, one minor versions at a time receives security advisories, the most recent minor release.

For example, Milex 3.1 will continue receiving security advisories until the release of Milex 3.2, and 3.2 will receive security advisories until the release of 3.3.

### Supported Versions

| Branch | Beta Release | Initial Release | Active Support Until | Security Support Until *
|--|--|--|--|--|
|1.0|17 May 2021|30 Aug 2021|29 Nov 2021|29 Nov 2021

\* = Security Support for 2.16 will only be provided for Milex itself, not for core dependencies that are EOL like Symfony 2.8.

Security advisories are only made for issues affecting stable releases in the supported major version branches. That means there will be no security advisories for development releases (-dev), alphas, betas or release candidates.


How to report a potential security issue
----------------------------------------

If you discover or learn about a potential error, weakness, or threat that can compromise the security of Milex and is covered by the [Security Advisory Policy](https://www.milex.org/milex-security-team/milex-security-advisory-policy), we ask you to keep it confidential and submit your concern to the Milex security team.

To make your report please submit it via [https://huntr.dev](https://huntr.dev).

Do not post it in Github, the forums, or or discuss it in Slack.

[Read more: How to report a security issue with Milex](https://www.milex.org/milex-security-team/how-to-report-a-security-issue)

How are security issues resolved?
---------------------------------

The Milex Security Team are responsible for triaging incoming security issues relating to Milex core and officially supported plugins, and for releasing fixes in a timely manner.

[Read more: How are security issues triaged and resolved by the Milex Security Team?](https://www.milex.org/milex-security-team/triaging-and-resolving-security-issues)

How are security fixes announced and released?
----------------------------------------------

The Security Team coordinates security announcements in release cycles and evaluates whether security issues are ready for release several days in advance.

The team may deem it necessary to make an out-of-sequence release, in which case at least two weeks’ notice will be provided to ensure that Milex users are made aware of a security release being made on an unscheduled basis.

[Read more: Security fix announcements and releases](https://www.milex.org/milex-security-team/triaging-and-resolving-security-issues)

What is a Security Advisory?
----------------------------

A security advisory is a public announcement managed by the Milex Security Team which informs Milex users about a reported security problem in Milex core or an officially supported plugin and the steps Milex users should take to address it. (Usually this involves updating to a new release of the code that fixes the security problem.)

[Read more: Milex Security Advisory Policy](https://www.milex.org/milex-security-team/milex-security-advisory-policy)

What is the disclosure policy of the Milex Security Team?
----------------------------------------------------------

The security team follows a Coordinated Disclosure policy: we keep issues private until there is a fix. Public announcements are made when the threat has been addressed and a secure version is available.

When reporting a security issue, observe the same policy. **Do not** share your knowledge of security issues with others.

How do I join the Milex Security Team?
---------------------------------------

As membership in the team gives the individual access to potentially destructive information, membership is limited to people who have a proven track record in the Milex community.

Team members are expected to work at least a few hours every month. Exceptions to that can be made for short periods to accommodate other priorities, but people who can't maintain some level of involvement will be asked to reconsider their membership on the team.

[Read more: How do I join the Milex Security Team?](https://www.milex.org/milex-security-team/join-the-team)

Who are the Milex Security Team members?
-----------------------------------------

You can meet the Milex Security Team on the page below.

[Read more: Meet the Milex Security Team](https://www.milex.org/meet-the-milex-security-team)

Resources and guidance from the [Drupal](https://www.drupal.org/security), [Joomla](https://developer.joomla.org/security.html) and [Mozilla](https://www.mozilla.org/en-US/security/) projects have been drawn from to create these documents and develop our processes/workflows.


Always [report the issue to the team](https://www.milex.org/milex-security-team/how-to-report-a-security-issue) and let them make the decision on whether to handle it in public or private.
