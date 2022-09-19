# Dorp Theme Extras

This module can be used in addition to the Dorp Theme. Some extra
functionality includes a styleguide and a drush command to generate a subtheme.


## Generating a subtheme

*   Clone this module to your codebase, enable it and clear all caches.
*   Clone `dorp` into the themes folder.
*   Run: `drush theme:new-theme "THEME_NAME"` or `drush nt "THEME_NAME"`
This command will clone and rename the 'THEME' folder and its contents to your
new theme name.


## Styleguide Twig (sgt)

The styleguide is found under `Appearance > Styleguide` or `/styleguide`.

The styleguide includes some of Drupal's code. E.g an example Drupal form,
error messages and pager. You can also add your own twig files from the active
theme to the styleguide.

The module will search for twig files in `{THEME}/_source/partials/` and
`{THEME}/templates/` with the comment below:

```twig
Styleguide: {
  title: Buttons,
  section: Components,
  description: Buttons description.,
  classes: sgt-grid background-blue, // Classes to add to the markup wrapper.
  width: 650px, // Adds a max-width to the wrapper.
}
```

Important: Each line should end in a comma.

So your file will look like this:
```twig
{#
/**
 * @file
 * Buttons
 *
 * Styleguide: {
 *  title: Buttons,
 *  section: Components,
 *  description: Buttons description.,
 * }
 *
 */
#}
<a href="{{ button.link }}" class="btn {{ button.class }}">{{ button.text }}</a>
```

You can set the variables for the styleguide by making a .json file with the
same name. E.g. `buttons.twig` => `buttons.json`.

The arrays and objects declared in these json files are converted to php and
passed through Drupal render. These means you can write render arrays in json.
It also creates the `{{ attributes }}` variable using the Drupal Attribute
class, so you can pass entity attributes to the template.

---

### Creating a color palette

To generate a color palette include a `_colors.scss` file under
`{THEME}/_source/partials/abstracts/`. For example:
```scss
/**
 * @file
 * Colors.
 */

// Styleguide: Brand Colors
$c-gray: #a8a8a8;
$c-badass: #bada55;

/* Styleguide: Greyscale Colors */
$white: #fff;
$black: #000;

// Styleguide: None
$c-alert: #A00;
```

Notes:
*   Any colors written under `// Styleguide: None` will not show.
*   For scss values to work in the color palette it depends on the
    scssphp/scssphp library. Do `composer install scssphp/scssphp`. This may
    require a restart of the container.

---

### JSON files

If you have added the _global.json file, you can add variables here that will
be shared across all templates. E.g. `title: "Lorem ipsum..."`. Redeclaring a
variable in the component json will override it for that component.

Using the drush command to create a new theme, it will ask if you want this
option and it will add it for you. Otherwise, it should be located in
`/_source/partials/abstracts/_global.json`. There's an example in the files
folder.

#### Drupal forms
In the Json file you can pass a Drupal form to a variable by passing the namespace of the form in `_getForm()`.

For example:

`"search_form": "_getForm(\Drupal\MODULE\Form\SearchForm)"`

---

### Example \_source directory:
`***` = Styleguide additions
```
partials/
|
|– abstracts/
|   |– _colors.scss           # Colors - *** Styleguide comment
|   |– _functions.scss        # Functions
|   |- _global.json           # *** Global JSON - Dummy data for styleguide
|   |– _helpers.scss          # Helpers
|   |– _mixins.scss           # Mixins
|   |– _variables.scss        # Variables
|
|– base/
|   |– _forms.scss            # Forms
|   |– _grid.scss             # Grid system
|   |– _icons.scss            # Icons
|   |– _messages.scss         # Drupal Messages
|   |– _tabs.scss             # Drupal Tabs
|   |– _typography.scss       # Typography
|
|– components/
|   |- buttons/
|   |   |– _buttons.scss      # Buttons scss
|   |   |– _buttons.json      # *** Buttons json - Dummy data for styleguide
|   |   |– buttons.twig       # Buttons twig - *** Styleguide comment
|   |– footer/
|   |   |- _footer.scss       # Footer scss
|   |   |- footer.twig        # Footer twig
|   …                         # Etc.
|
|– styleguide/                # *** Optional - for styleguide only
|   |– grid.twig              # Grid example
|   |– typography.twig        # Typography example
|   …                         # Etc.
|
images/
|   …
`– script.js                  # Main JS file
`– style.scss                 # Main Sass file
```

## JSON examples:

```
{
  // String: title => 'This is teaser heading',
  "title": "This is teaser heading",
  "url": "#",
  // THIS IS RENDERED AS A DRUPAL RENDER ARRAY https://www.drupal.org/docs/drupal-apis/render-api/render-arrays
  "text": {
    "#markup": "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>"
  },
  // THIS WOULD BE RENDERED AS DRUPAL IMAGES
  "image": {
    "#theme": "image",
    "#uri": "/core/modules/image/sample.png"
  },
  "image_style": {
    "#theme": "image_style",
    "#uri": "/core/modules/image/sample.png",
    "#style_name": "large"
  },
  "responsive_image": {
    "#theme": "responsive_image",
    "#uri": "/core/modules/image/sample.png",
    "#responsive_image_style_id": "carousel"
  },
  // AN ARRAY OF STRINGS
  "meta": [
    "Friday 21st August 2020 9am",
    "London",
    "North London Support Group"
  ],
  "cta": {
    "#markup": "<a href=\"#\">CTA link</a>"
  },
  // USING _getForm() IS THE SAME AS \Drupal::formBuilder()->getForm("\Drupal\[module]\Form\CustomForm");
  "form": "_getForm(\Drupal\[module]\Form\CustomForm)",
  // AN ARRAY THAT REPEATS AND OVERRIDES TOP-LEVEL VARS FOR EACH VARIATION
  "sgt_repeat": [
    {
      "title": "title",
      "meta": [
        "Monday 21st August 2020 9am",
        "Bristol",
        "North London Support Group"
      ]
    },
    {
      "title": "title 3",
      // "FALSE" MEANS IT WON'T SHOW
      "cta": false,
      "text": {
        "#markup": "<p>Hey</p>"
      }
    }
  ],
  "sgt_repeat_wrapper": false, // REMOVES THE SGT_REPEAT WRAPPER DIVS
  "sgt_repeat_class": "row-1" // ADDS CLASSES TO THE SGT_REPEAT WRAPPER DIVS
}
```
