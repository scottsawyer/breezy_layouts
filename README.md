# Breezy Layouts

A configurable set of layouts with utility classes in mind, particularly [TailwindCSS](https://tailwindcss.com/).

The main idea of this module is to use Tailwind utility classes to create layout variants.  It includes a form builder for creating fields that will inject Tailwind classes into layouts.  Using the form, a site builder can give editors as much or as little control over the variants as desired.

Special thanks to the [Webform team](https://www.drupal.org/project/webform), this module borrows heavily from Webform for the form builder.

## Primary concepts

The module is designed to work with the provided Breezy Layouts and core Breakpoints.

Currently, it only works with a subset of Tailwind utility classes, but the plan is to allow any utility classes.

The provided layouts are unopinionated out of the box.  The expectation is, the site builder will create "variants" of the desired layouts.

Variants are plugins that work with specific layouts.

So, for example, with the 2 column layout, you could create 3 variants:
 - 50 / 50
 - 33 / 66
 - 66 / 33

Further, you could have variants for full width vs contained.  It just depends on how you configure your variants.

Additionally, you could give your editors the ability to select any number of options for the variant by adding fields.

When the editor selects a layout from the configured variants.

## Limitations

This module does not currently support every Tailwind class, nor does it support some of the more advanced features such as class chaining or arbitrary styles.

There are plans to adding more classes, as well as adding support for other utility class frameworks.  But first things first.

## Initial configuration

After enabling the module, visit /admin/config/content/breezy-layouts/settings.

Here you can select registered layouts the layouts the for variants.

If you are just getting started, select all the Breezy Layouts.

Next, you'll select a breakpoint group.  Again, any breakpoint group should work, but if you want to use Tailwind, the Breezy Layouts breakpoint group is mirrors the breakpoints of Tailwind.

After you select a breakpoint group, fields for prefixing your breakpoint-based classes are displayed.  See the [TailwindCSS Responsive Design docs](https://tailwindcss.com/docs/responsive-design).

> **NOTE** TailwindCss does not prefix the "Mobile" breakpoint.  Also, be sure to include any separator.  TailwindCSS uses a colon ":".

Lastly, for prototyping / testing, you can include a CDN.  TailwindCSS uses https://cdn.tailwindcss.com.

> **NOTE** The CDN should not be used for production, as there is a performance penalty, and it does not support all TailwindCSS features.

> **NOTE** If you are using a build process, be sure to include breezy_layouts.breezy_layouts_variant.*.yml config files.

## Creating variants

When you are ready to create a variant, visit /admin/config/content/breezy-layouts/variants and click "Add variant".

Give the variant a meaningful name, as this is the label the editor will see when the layout is selected.

Next, choose a layout and click Create.  You should be redirected to the variant edit screen.

The edit screen is organized by the breakpoints defined in the initial configuration.

Enable each breakpoint to see the configurable portions of the layout.  Breezy Layouts have optional configurations for:
- Container (optional)
- Wrapper
- Each region

If you do not add any properties in the Container portion, the Container div will not be rendered.

For example, the Two Column layout with a Container:
```
<div class="container ..."> <!-- Container -->
  <div class="..."> <!-- Wrapper -->
    <div class="..."> <!-- Left -->
    </div>
    <div class="..."> <!-- Right -->
    </div>
  </div>
</div>
```


The result of a variant with no container.
```
<div class="..."> <!-- Wrapper -->
  <div class="..."> <!-- Left -->
  </div>
  <div class="..."> <!-- Right -->
  </div>
</div>
```



> **NOTE** Breezy Layouts does not support the advanced Container properties.  If there are any properties added to the Container in any breakpoint, the Container will be rendered with the `container` class.  [Read more about Tailwind containers.](https://tailwindcss.com/docs/container)

### Adding a property to portion of the layout:

Breezy Layouts are organized by CSS properties.  You can add as many properties as you desire, even add the same property multiple times.

1. Click "Add property".  This opens a modal where you can select a CSS property.
2. Select a property.  This reveals different form elements.

#### Field types
Currently, Breezy Layouts supports four field types:
1. Select
2. Hidden
3. Checkboxes
4. Radios

Which field you choose depends on your strategy for how you want your editors to configure the property.

**Hidden**
Choose "Hidden" when you want the CSS class to always be selected.

**Checkboxes**
Choose "Checkboxes" when you want the editor to choose multiple options.

**Radios**
Choose "Radios" when you want the editor to choose only one of the options AND the list of options is relatively short.

**Select**
Choose "Select" when you want the editor to choose only one of the options AND the list of options is relatively long.

> For more information about when to choose Radios vs Select inputs, see [UX Design World's post](https://uxdworld.com/2018/05/06/7-rules-of-using-radio-buttons-vs-drop-down-menus/) on the subject.

#### Configure the field.

**Enter a descriptive label.**

This is important for both your editors to understand what they are configuring as well as your own sanity.

The label will generate a "key" for the property.  This key must be unique for the breakpoint / portion.

**Hidden**

Hidden fields support adding a single class.  Choose from the available classes and save.

**Radios, Checkboxes, and Select**

Radios, Checkboxes, and Select all have a similar configuration.

**Required or not**

This will make the field required.  If you find you are adding a lot of required fields, maybe consider adding these properties as hidden fields.

**Add some options**

For each option, select a class and add a label.  The labels can be text.  Think about your users.

> **NOTE** This module makes no assumption about the classes you select.  It is totally possible to create conflicting or useless options. You should have a solid understanding of CSS.

**Save**

This closes the model and you should see your newly configured field.  There is no need to save at this point.

Once you've added some properties, you can move the fields around within the portion of the layout.  Changing the field order or any other changes requires saving.

You will repeat this process for each layout for which you would like to create a variant.

## Configuring Layouts

Once you have one or more enabled variants for a layout, whenever that layout is selected (using Layouts Builder, Layout Paragraphs, etc), the variants will appear as options.  Choosing the variant will expose the configured field, if there are any fields with a UI (not hidden fields).




