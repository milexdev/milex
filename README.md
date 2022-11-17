[![codecov](https://codecov.io/gh/milex/milex/branch/features/graph/badge.svg)](https://codecov.io/gh/milex/milex)
<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
[![All Contributors](https://img.shields.io/badge/all_contributors-82-orange.svg?style=flat-square)](#contributors-)
<!-- ALL-CONTRIBUTORS-BADGE:END -->

About Milex
============
Milex is the world’s largest open source marketing automation project. With over 200,000 organisations using Milex and over 1,000 community volunteers, we empower businesses by making it easy to manage their marketing across a range of channels. Stay up to date about initiatives, releases and strategy via our [blog][milex-blog].

Marketing automation has historically been difficult to implement within organisations. The Milex Community is an example of open source at its best, offering great software and a vibrant and caring community in which to learn and share knowledge.

Open source means more than open code. Open source provides equality for all and a chance for everyone to improve.

![Milex](.github/readme_image.png "Milex Open Source Marketing Automation")

Get Involved
=============
Before we tell you how to install and use Milex, we like to shamelessly plug our awesome user and developer communities! Users, start [here][get-involved] for inspiration, or follow us on Twitter [@MilexCommunity][twitter] or Facebook [@MilexCommunity][facebook]. Once you’re familiar with using the software, maybe you will share your wisdom with others in our [Slack][slack] channel.

Calling all devs, testers and tech writers! Technical contributions are also welcome. First, read our [general guidelines][contributing] about contributing. If you want to contribute code, read our [CONTRIBUTING.md][contributing-md] or [Contributing Code][contribute-developer] docs then check out the issues with the [T1 label][t1-issues] to get stuck in quickly and show us what you’re made of.

If you have questions, the Milex Community can help provide the answers.

Installing and using Milex
============================

## Supported Versions

| Branch | RC Release | Initial Release | Active Support Until | Security Support Until*
|--|--|--|--|--|
|1.x   | 22 May 2022 | 30 May 2022 | 30 May 2022 | 20 Dec 2022

`*`Security support for 2.16 will only be provided for Milex itself, not for core dependencies that are EOL, such as Symfony 2.8.

## Software Downloads
The GitHub version is recommended for both development and testing. The production package (including all libraries) is available at [milex.org/download][download-milex].

## Installation
### Disclaimer
*Install from source only if you are comfortable using the command line. You'll be required to use various CLI commands to get Milex working and keep it working. If the source/database schema gets out of sync with Milex releases, the release updater may not work and will require manual updates. For production, we recommend the pre-packaged Milex which is available at [milex.org/download][download-milex].*

*Also note that source code outside of a [tagged release][tagged-release] should be considered ‘alpha’. It may contain bugs, cause unexpected results, data corruption or loss, and is not recommended for use in a production environment. Use at your own risk.*

### How to install Milex
You must already have [Composer][composer] available on your computer because this is a development release and you'll need Composer to download the vendor packages.

Also note that if you have DDEV installed, you can run 'ddev config' followed by 'ddev start'. This will kick off the Milex first-run process which will automatically install dependencies and configure Milex for use. ✨ 🚀 Read more [here][ddev-milex]

Installing Milex is a simple three-step process:

1. [Download the repository zip][download-zip] then extract the zip to your web root.
2. Run the `composer install` command to install the required packages.
3. Open your browser and complete the installation through the web installer.

If you get stuck, check our our [general troubleshooting][troubleshooting] page. Still no joy? Join our lively [Milex Community][community] for support and answers.

### User Documentation
Documentation on how to use Milex is available at [docs.milex.org][milex-docs].

### Developer Docs
Developer documentation, including API reference docs, is available at [developer.milex.org][dev-docs].


## Contributors ✨

Thanks goes to these wonderful people ([emoji key](https://allcontributors.org/docs/en/emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tr>
    <td align="center"><a href="https://twitter.com/eprem9"><img src="https://avatars.githubusercontent.com/u/5460763?s=400&v=4" width="100px;" alt=""/><br /><sub><b>Yeprem Ghukasyan</b></sub></a><br /></td>
  </tr>
</table>

<!-- markdownlint-restore -->
<!-- prettier-ignore-end -->

<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors][all-contributors] specification. Contributions of any kind welcome!

[milex-blog]: <https://www.milex.org/blog>
[get-involved]: <https://www.milex.org/community/get-involved>
[twitter]: <https://twitter.com/MilexCommunity>
[facebook]: <https://www.facebook.com/MilexCommunity/>
[slack]: <https://www.milex.org/community/get-involved/communication-channels>
[contributing]: <https://contribute.milex.org/contributing-to-milex>
[contributing-md]: <https://github.com/milex/milex/blob/feature/.github/CONTRIBUTING.md>
[contribute-developer]: <https://contribute.milex.org/contributing-to-milex/developer>
[t1-issues]: <https://github.com/milex/milex/issues?q=is%3Aissue+is%3Aopen+label%3AT1>
[download-milex]: <https://www.milex.org/download>
[tagged-release]: <https://github.com/milex/milex/releases>
[composer]: <http://getcomposer.org/>
[download-zip]: <https://github.com/milex/milex/archive/refs/heads/features.zip>
[ddev-milex]: <https://kb.milex.org/knowledgebase/development/how-to-install-milex-using-ddev>
[troubleshooting]: <https://docs.milex.org/en/troubleshooting>
[community]: <https://www.milex.org/community>
[milex-docs]: <https://docs.milex.org>
[dev-docs]: <https://developer.milex.org>
[all-contributors]: <https://github.com/all-contributors/all-contributors>
"# milex" 
