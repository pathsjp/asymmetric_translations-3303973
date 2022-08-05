# Asymmetric Translations

## Why does this module exist?
The philosophy of Drupal core translations is that a translation of a page (a node) is a one on one translation of the source. 
This can be a good philosophy for some projects: for example wikipedia.org would probably best provide the exact same information for every language.

However, in practice we often encounter scenarios where this is not completely applicable and we need to implement "dirty" workarounds to get what we want. 
Especially the fact that each and every paragraph of the paragraphs module needs to be there, often turns out to be quite a problem. 
For example, a node may contain a "product promotion" paragraph which at one time applies only to a specific country / language. 
Another scenario we often encounter is the fact that clients sometimes want a node in one language to have all bells and whistles, while in another language the page only needs a little bit of text.

In Drupal 7 one had the choice to set up the translations in one way or the other: it either worked pretty much the same as it now works in Drupal 8+, 
or you could set it up so that each translation had it's own node. Now this is where the Asymmetric Translations module comes in.

## Module philosophy
The idea behind this module is that content itself is not translatable, but is created for a specific language. 
A parent entity (the so called Asymmetric Node Translation - or ANT in short) is translatable. 
Each ANT translation refers to a node of that same language, or does not refer to a node, meaning there is no translation in that specific language.

This approach offers the possibility for a node to have a different amount of paragraphs than a node in another language, or the node can even be of a whole different node bundle.

As for the HTML standards you could say that this module reckons a page translation as no more than a set of hreflang references telling the website visitor / bot where to find the translations.

## The state of this module
This module has been developed to be used in (and is being used in) a production environment. While the module is being used actively already, it should not be considered as stable. 
The main features seem to be working well (refence nodes to other languages, have a working language dropdown, automatically overwrite the hreflang metatags). 
There are however still (plenty of) missing features, there are not tests, missing documentation, many todo's, etc. etc.

## Module alternatives

### Soft Translations
The [Soft Translations](https://www.drupal.org/project/soft_translations) module has the same goal as this module.
The Soft Translations module seem to be build around autodetection of the same URL.

When starting this module the consideration was to contribute to the Soft Translations module or create a new one. 
Since the expected architecture of the module we needed was to far off, a new module seemed to be a better approach. 
That said, the modules may lend from each other and even a merge may seem appropiate at one time.

### Paragraphs Asymmetric Translation Widgets
The [Paragraphs Asymmetric Translation Widgets](https://www.drupal.org/project/paragraphs_asymmetric_translation_widgets) module provides asymmetric translations for Paragraphs.

While the module is a great effort to achieve asymmetric translations, the possible impact of a bug can be huge and leading to data loss. While it works most of the time, 
we have encountered paragraphs detached from the parent node (and thus "lost") and paragraphs assigned to an incorrect node language.

The **Asymmetric Translations** module takes another approach in which the worst case scenario would be to 
lose the refences between one or more translations of the same page, but the nodes / paragraphs itself will never be impacted.

## Good to know / considerations
### No involvement in core translations
When calling `$node->getTranslations()` this module will not be involved, and thus this method call will not return any translations, unless there are core translations. 
This has been a design choice whether or not to involve in core functionality. Apart from the fact that it is really hard 
(or even impossible) for the module to hook into such functionality, it also seemed appropiate to not do that.

### Choices made when creating the Asymmetric Node Translations entity

- The entity type is not fieldable. The only field needed is an antity refence field to reference to the node, which is a basefield of the entity
- The entity type is not revisionable. Making the entity type revionable would add a lot more complexity which we currently don't want.
- The entity type is translatable. Each language contains a reference (or no reference) to a node in that specific language
- The entity type has no bundles. In the future we might want to create for example Asymmetric Taxonomy Term Translations (not sure if that would be a thing actually), but such a new feature would need a whole new submodule and entity type - not a bundle
- The entity type has no templates
- The entity type has it's own CRUD permissions. It probably would be better if the entity would follow the permissions of the referenced node types somehow.
- The entity type has no title. The title resides in the referenced node.
- The entity type has no status. The status resides in the referenced node. When there are no nodes referenced, the entity should no longer exist.
- The entity type has a "created" and a "changed" field, which might help debugging some issues.
- The entity type has no author

## (Known) Todo's

- Update this documentation with screenshots, how to use (code examples), etc.
- Update this documentation with dependency info (quick node clone)
- Update this documentation on how to use the submodules
- Update this documentation with info about conflicts between core translations and this module and how this module handles them
- Fix all "TODO" references in code
- Improve all texts and make them translatable
- Run against Drupal code standards (etc.)
- Write a cron function that checks for empty Asymmetric Translation entities (referencing to nothing) and remove them
