Analytics Snippet (module for Omeka S)
======================================

> __New versions of this module and support for Omeka S version 3.0 and above
> are available on [GitLab], which seems to respect users and privacy better
> than the previous repository.__

[Analytics Snippet] is a module for [Omeka S] that allows the global admin to
add a snippet, generally a javascript tracker, at the end of the public pages
and/or at the end of admin pages. It can track json and xml requests too via
sub-modules.

It’s primarly designed for open source analytics platforms, like [Matomo (formerly Piwik)]
or [Open Web Analytics], but it can be used with any other competitor services like [Woopra],
[Google Analytics] or [Heap Analytics], if you don’t fear to give the life of
your visitors for free or by paying to people who will manipulate them or sell
them with a big profit. Any other javascript or html code can be added too.

Sub-modules can be enabled too to track api json and xml calls, for example [Analytics Snippet Matomo/Piwik].

**Important**: to get statistics on keywords used by visitors in search engines
(Yahoo, Google, Bing, etc.) to find your site, you need to allow it via a specific
account on each search engine. See [Matomo help for more information].


Installation
------------

See general end user documentation for [installing a module].

This module requires the module [Common], that should be installed first.

* From the zip

Download the last release [AnalyticsSnippet.zip] from the list of releases,
and uncompress it in the `modules` directory.

* From the source and for development

If the module was installed from the source, rename the name of the folder of
the module to `AnalyticsSnippet`.


Usage
-----

The code can be set in the config of the module and/or in the site settings.

Note: For technical reasons, the html code must start with `<!DOCTYPE html>`,
without useless space or line break at the beginning. This is the default on
most of the themes.

To get keywords used by visitors in search engine, enable it via an account in
each search engine. See [Matomo help for more information].


Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.


Troubleshooting
---------------

See online issues on the [module issues] page on GitLab.


License
-------

This module is published under the [CeCILL v2.1] license, compatible with
[GNU/GPL] and approved by [FSF] and [OSI].

This software is governed by the CeCILL license under French law and abiding by
the rules of distribution of free software. You can use, modify and/ or
redistribute the software under the terms of the CeCILL license as circulated by
CEA, CNRS and INRIA at the following URL "http://www.cecill.info".

As a counterpart to the access to the source code and rights to copy, modify and
redistribute granted by the license, users are provided only with a limited
warranty and the software’s author, the holder of the economic rights, and the
successive licensors have only limited liability.

In this respect, the user’s attention is drawn to the risks associated with
loading, using, modifying and/or developing or reproducing the software by the
user in light of its specific status of free software, that may mean that it is
complicated to manipulate, and that also therefore means that it is reserved for
developers and experienced professionals having in-depth computer knowledge.
Users are therefore encouraged to load and test the software’s suitability as
regards their requirements in conditions enabling the security of their systems
and/or data to be ensured and, more generally, to use and operate it in the same
conditions as regards security.

The fact that you are presently reading this means that you have had knowledge
of the CeCILL license and that you accept its terms.


Copyright
---------

* Copyright Daniel Berthereau, 2017-2025 (see [Daniel-KM] on GitLab)


[Analytics Snippet]: https://gitlab.com/Daniel-KM/Omeka-S-module-AnalyticsSnippet
[Omeka S]: https://omeka.org/s
[Matomo (formerly Piwik)]: https://matomo.org
[Matomo help for more information]: https://matomo.org/faq/reports/analyse-search-keywords-reports
[Open Web Analytics]: http://www.openwebanalytics.com
[Woopra]: https://www.woopra.com
[Google Analytics]: https://www.google.com/analytics
[Heap Analytics]: http://heapanalytics.com
[Analytics Snippet Matomo/Piwik]: https://gitlab.com/Daniel-KM/Omeka-S-module-AnalyticsSnippetPiwik
[installing a module]: https://omeka.org/s/docs/user-manual/modules/#installing-modules
[Common]: https://gitlab.com/Daniel-KM/Omeka-S-module-Common
[AnalyticsSnippet.zip]: https://github.com/Daniel-KM/Omeka-S-module-AnalyticsSnippet/releases
[module issues]: https://gitlab.com/Daniel-KM/Omeka-S-module-AnalyticsSnippet/-/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[GitLab]: https://gitlab.com/Daniel-KM
[Daniel-KM]: https://gitlab.com/Daniel-KM "Daniel Berthereau"
