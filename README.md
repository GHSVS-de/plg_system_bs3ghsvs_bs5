# plg_system_bs3ghsvs (BOOTSTRAP 5)
- Joomla system plugin to register and override JHtml helpers. And more more more.
- Plugin for ghsvs.de templates.
- **Don't use it if you don't need it.**

# Be aware
- It replaces, respectively updates, the Bootstrap 4 plugin https://github.com/GHSVS-de/plg_system_bs3ghsvs if already installed on your site!
- This one here uses the same folders and deletes outdated files and folders from the old installation.
- Internally it has the same name like the older
- Code changes, new features etc. will NOT be backported to the old plugin.

### Replacements. Conditional requirements.
- - @since V2021.07.04: If you want to use the **"Structured Data"** feature of the plugin you have to install [GHSVS-de/pkg_lib_structuredataghsvs](https://github.com/GHSVS-de/pkg_lib_structuredataghsvs/releases) separately in your Joomla.
- - @since V2021.07.12: If you want to use the **"Image Resize"** feature of the plugin you have to install [GHSVS-de/pkg_lib_imgresizeghsvs](https://github.com/GHSVS-de/pkg_lib_imgresizeghsvs/releases) separately in your Joomla.

-----------------------------------------------------

# My personal build procedure (WSL 1, Debian, Win 10)

**@since v2022.06.14: Build procedure uses local repo fork of https://github.com/GHSVS-de/buildKramGhsvs**

## Then
- Prepare/adapt `./package.json`.
- `cd /mnt/z/git-kram/plg_system_bs3ghsvs_bs5`

## node/npm updates/installation
- `npm install` (if never done before)

### Update dependencies
- `npm run updateCheck` or (faster) `npm outdated`
- `npm run update` (if needed) or (faster) `npm update --save-dev`

## Build installable ZIP package
- `node build.js`
- New, installable ZIP is in `./dist` afterwards.
- All packed files for this ZIP can be seen in `./package`. **But only if you disable deletion of this folder at the end of `build.js`**.

### For Joomla update and changelog server
- Create new release with new tag.
  - See and copy and complete release description in `dist/release_no-changelog.txt`.
- Extract of the update XML for update servers are in `./dist` as well. Copy/paste into update xml.
