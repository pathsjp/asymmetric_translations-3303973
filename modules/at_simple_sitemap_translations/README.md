# Simple Sitemap Translations

If using the simple_sitemap module to generate a sitemap.xml file, you should
enable this module to add the Asymmetric Node Translations to the sitemap.

This module hooks into `hook_simple_sitemap_links_alter`.
Our hook will loop over each sitemap entry.
If it is a node, it will check if there are asymmetric translations defined for the node.
If so, it will add the translations to the translations array, but only if the language isn't yet in the array.
