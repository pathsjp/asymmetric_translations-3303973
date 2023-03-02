# Filter Node Reference Widgets

## Important changes
- As of version 1.0.0-alpha4, the custom entity reference view is installed automatically
- As of version 1.0.0-alpha5, link-fields and entity-reference-fields that reference nodes and have the default
reference settings, are now automatically overruled by using the custom view.

## What does it do?
When installing this module, automatically a new view `Entity Reference By Language`
is added.
When the CMS user uses an entity reference field to link to nodes,
only relevant nodes are shown, i.e. nodes in the current language.

## How to configure?
When creating a new entity reference field that references nodes, the filter view
can be used to filter out all the languages except the current language.
On the settings page for the entity reference field, use the setting `Views: Filter
by an entity reference view` and select `entity_reference_by_language - Entity
Reference` in the next dropdown.
