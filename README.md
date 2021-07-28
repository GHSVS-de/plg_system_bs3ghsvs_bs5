# plg_system_bs3ghsvs (BOOTSTRAP 5)
- Joomla system plugin to register and override JHtml helpers. And more more more.
- Plugin for ghsvs.de templates.
- **Don't use it if you don't need it.**

# Be aware
- It replaces, respectively updates, the Bootstrap 4 plugin https://github.com/GHSVS-de/plg_system_bs3ghsvs if already installed on your site!
- This one here uses the same folders and deletes outdated files and folders from the old installation.
- Internally it has the same name like the older
- Code changes, new features etc. will only be backported to the old plugin when I think it makes sense.
- Composer/Vendor actions completely removed since V2021.07.04/12
- - @since V2021.07.04: If you want to use the "Structured Data" feature of the plugin you have to install [GHSVS-de/pkg_lib_structuredataghsvs](https://github.com/GHSVS-de/pkg_lib_structuredataghsvs/releases) separately in your Joomla.
- - @since V2021.07.12: If you want to use the "Image Resize" feature of the plugin you have to install [GHSVS-de/pkg_lib_imgresizeghsvs](https://github.com/GHSVS-de/pkg_lib_imgresizeghsvs/releases) separately in your Joomla.

# Changelog
- https://updates.ghsvs.de/changelog.php?file=plg_system_bs3ghsvs_bs5

# My personal build procedure

## Don't forget this step when new Bootstrap JS!
- https://github.com/GHSVS-de/tpl_bs4ghsvs#readme

## npm. Create new Joomla extension installation package

###
- Clone repository into your server environment (WSL or whatever).

- `cd /mnt/z/git-kram/plg_system_bs3ghsvs_bs5`

- Check/edit `/package.json` and add plugin `version` and further settings like `minimumPhp` and so on. Will be copied during build process into manifest XML.
- **Do not overlook the new parameter `nameReal`!**
- Check also versions of dependencies, devDependencies. `npm run g-npm-update-check` and `npm run g-ncu-override-json`

### "Download" JS/CSS packages into `/node_modules`

- I you want to check first: `npm run g-npm-update-check`
- If you want to adapt package.json automatically first: `npm run g-ncu-override-json`

- `npm install`

OR

- `npm update`

. IGNORE: `npm WARN bootstrap@ requires a peer of popper.js@ but none is installed. You must install peer dependencies yourself.`

### Build new Joomla package ZIP.

- <strike>`nvm use 12` or `nvm use 13` to get rid of f'ing messages of NodeJs 14 that nobody understands but the creators and JS professors. Only `node build.js --svg` has still problems.</strike>

#### Whenever Bootstrap/icons or fontawesome/icons have been updated while `npm update`
- `node build.js --svg` (to create embeddable icons in `media/svgs/`)

#### else
- `node build.js`

#### if you have new icons

- <strike>`php bin/icons-html.php`</strike> (Meanwhile included in `build.js`.
- Creates `/dist/icons-overview.html` (an overview with all icons).

#####
- New ZIP is in `/dist/`
- FYI: Packed files for this ZIP can be seen in `/package/`. **But only if you disable deletion of this folder at the end of build.js**.

#### For Joomla update server
- Create new release with new tag.
- Get download link for new `dist/plg_blahaba_blubber...zip` **inside new tag branch** and add to release description and update server XML.
