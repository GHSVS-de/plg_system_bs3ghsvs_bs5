#!/usr/bin/env node
const path = require('path');

/* Configure START */
const pathBuildKram = path.resolve("../buildKramGhsvs");
const updateXml = `${pathBuildKram}/build/update.xml`;
const changelogXml = `${pathBuildKram}/build/changelog.xml`;
const releaseTxt = `${pathBuildKram}/build/release.txt`;
/* Configure END */

const replaceXml = require(`${pathBuildKram}/build/replaceXml.js`);
const helper = require(`${pathBuildKram}/build/helper.js`);

const pc = require(`${pathBuildKram}/node_modules/picocolors`);
//const fse = require(`${pathBuildKram}/node_modules/fs-extra`);

let replaceXmlOptions = {};
let zipOptions = {};
let from = "";
let to = "";

const {
	filename,
	name,
	version,
} = require("./package.json");

const manifestFileName = `${filename}.xml`;
const Manifest = `${__dirname}/package/${manifestFileName}`;
const pathMedia = `./media`;
let versionSub = '';

// Dummy. Just annoying to adapt replaceXml calls.
const thisPackages = [];

(async function exec()
{
	let cleanOuts = [
		`./package`,
		`./dist`,
		`${pathMedia}/fontawesome-free`,
		`${pathMedia}/scss/bootstrap`,
		`${pathMedia}/css/bootstrap`,
		`${pathMedia}/js/bootstrap`,
		`${pathMedia}/js/jquery`,
		`${pathMedia}/js/jquery-migrate`,
	];
	await helper.cleanOut(cleanOuts);

	await helper.mkdir('./dist');

	// Get subversion (Bootstrap icons):
	from = `${__dirname}/node_modules/bootstrap/package.json`;
	versionSub = await helper.findVersionSubSimple (from, 'bootstrap');
	console.log(pc.magenta(pc.bold(`versionSub identified as: "${versionSub}"`)));

	// ### Prepare /media/.
	// #### Fontawesome without SVGs.
	for (const file of ['css', 'scss', 'webfonts', 'LICENSE.txt', 'package.json'])
	{
		from = `./node_modules/@fortawesome/fontawesome-free/${file}`;
		to = `${pathMedia}/fontawesome-free/${file}`;
		await helper.copy(from, to);
	}

	// #### Bootstrap.
	for (const file of ['js', 'css'])
	{
		from = `./node_modules/bootstrap/dist/${file}`;
		to = `${pathMedia}/${file}/bootstrap`;
		await helper.copy(from, to);
	}

	// #### More Bootstrap.
	from = "./node_modules/bootstrap/js/dist";
	to = `${pathMedia}/js/bootstrap/plugins`;
	await helper.copy(from, to);

	// #### More Bootstrap.
	from = "./node_modules/bootstrap/scss";
	to = `${pathMedia}/scss/bootstrap`;
	await helper.copy(from, to);

	// #### JQuery.
	from = `./node_modules/jquery/dist`;
	to = `${pathMedia}/js/jquery`;
	await helper.copy(from, to);

	// #### JQuery-migrate.
	from = `./node_modules/jquery-migrate/dist`;
	to = `${pathMedia}/js/jquery-migrate`;
	await helper.copy(from, to);

	// ## /media/.
	from = pathMedia;
	to = `./package/media`;
	await helper.copy(from, to);

	// ## /src/.
	from = `./src`;
	to = `./package`;
	await helper.copy(from, to);

	const zipFilename = `${name}-${version}_${versionSub}.zip`;

	replaceXmlOptions = {
		"xmlFile": Manifest,
		"zipFilename": zipFilename,
		"checksum": "",
		"dirname": __dirname
	};

	await replaceXml.main(replaceXmlOptions);
	from = Manifest;
	to = `./dist/${manifestFileName}`;
	await helper.copy(from, to)

	// ## Create zip file and detect checksum then.
	const zipFilePath = path.resolve(`./dist/${zipFilename}`);

	zipOptions = {
		"source": path.resolve("package"),
		"target": zipFilePath
	};
	await helper.zip(zipOptions)

	replaceXmlOptions.checksum = await helper._getChecksum(zipFilePath);

	// Bei diesen werden zuerst Vorlagen nach dist/ kopiert und dort erst "replaced".
	for (const file of [updateXml, changelogXml, releaseTxt])
	{
		from = file;
		to = `./dist/${path.win32.basename(file)}`;
		await helper.copy(from, to)

		replaceXmlOptions.xmlFile = path.resolve(to);
		await replaceXml.main(replaceXmlOptions);
	}

	cleanOuts = [
		`${pathMedia}/fontawesome-free`,
		`${pathMedia}/scss/bootstrap`,
		`${pathMedia}/css/bootstrap`,
		`${pathMedia}/js/bootstrap`,
		`${pathMedia}/js/jquery`,
		`${pathMedia}/js/jquery-migrate`,
		`./package`,
	];
	await helper.cleanOut(cleanOuts).then(
		answer => console.log(pc.cyan(pc.bold(pc.bgRed(
			`Finished. Good bye!`))))
	);
})();
