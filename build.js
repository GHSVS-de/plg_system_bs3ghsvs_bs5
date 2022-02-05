const fse = require('fs-extra');
const pc = require('picocolors');
const path = require('path');
const replaceXml = require('./build/replaceXml.js');
const helper = require('./build/helper.js');

const {
	filename,
	name,
	version,
} = require("./package.json");

const manifestFileName = `${filename}.xml`;
const Manifest = `${__dirname}/package/${manifestFileName}`;
const pathMedia = `./media`;
let versionSub = '';

// Just easier to handle in console.log:
let from = '';
let to = '';

// Dummy. Just annoying to adapt replaceXml calls.
const thisPackages = [];

(async function exec()
{
	// This is for SCSS compilation for tpl_bs4ghsvs. /git-kram/media/,
	const externalScssFolder = path.join(__dirname, '../', 'media/plg_system_bs3ghsvs/scss/bootstrap');

	let cleanOuts = [
		`./package`,
		`./dist`,
		`${pathMedia}/fontawesome-free`,
		`${pathMedia}/scss/bootstrap`,
		`${pathMedia}/css/bootstrap`,
		`${pathMedia}/js/bootstrap`,
		`${pathMedia}/js/jquery`,
		`${pathMedia}/js/jquery-migrate`,
		externalScssFolder,
	];
	await helper.cleanOut(cleanOuts);

	to = './dist'
	await fse.mkdir(to).then(
		answer => console.log(pc.yellow(pc.bold(`Created "${to}".`)))
	);

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
		await fse.copy(from, to
		).then(
			answer => console.log(
				pc.yellow(pc.bold(`Copied "${from}" to "${to}".`))
			)
		);
	}

	// #### Bootstrap.
	for (const file of ['js', 'css'])
	{
		from = `./node_modules/bootstrap/dist/${file}`;
		to = `${pathMedia}/${file}/bootstrap`;
		await fse.copy(from, to
		).then(
			answer => console.log(
				pc.yellow(pc.bold(`Copied "${from}" to "${to}".`))
			)
		);
	}

	// #### More Bootstrap.
	from = "./node_modules/bootstrap/js/dist";
	to = `${pathMedia}/js/bootstrap/plugins`;
	await fse.copy(from, to
	).then(
		answer => console.log(
			pc.yellow(pc.bold(`Copied "${from}" to "${to}".`))
		)
	);

	// #### More Bootstrap.
	from = "./node_modules/bootstrap/scss";
	to = `${pathMedia}/scss/bootstrap`;
	await fse.copy(from, to
	).then(
		answer => console.log(
			pc.yellow(pc.bold(`Copied "${from}" to "${to}".`))
		)
	);

	// #### More Bootstrap.
	// This is for SCSS compilation for tpl_bs4ghsvs. /git-kram/media/.
	from = `${pathMedia}/scss/bootstrap`;
	to = externalScssFolder;
	await fse.copy(from, to
	).then(
		answer => console.log(
			pc.yellow(pc.bold(`Copied "${from}" to "${to}".`))
		)
	);

	// #### JQuery.
	from = `./node_modules/jquery/dist`;
	to = `${pathMedia}/js/jquery`;
	await fse.copy(from, to
	).then(
		answer => console.log(
			pc.yellow(pc.bold(`Copied "${from}" to "${to}".`))
		)
	);

	// #### JQuery-migrate.
	from = `./node_modules/jquery-migrate/dist`;
	to = `${pathMedia}/js/jquery-migrate`;
	await fse.copy(from, to
	).then(
		answer => console.log(
			pc.yellow(pc.bold(`Copied "${from}" to "${to}".`))
		)
	);

	// ## /media/.
	from = pathMedia;
	to = `./package/media`;
	await fse.copy(from, to
	).then(
		answer => console.log(
			pc.yellow(pc.bold(`Copied "${from}" to "${to}".`))
		)
	);

	// ## /src/.
	from = `./src`;
	to = `./package`;
	await fse.copy(from, to
	).then(
		answer => console.log(
			pc.yellow(pc.bold(`Copied "${from}" to "${to}".`))
		)
	);

	const zipFilename = `${name}-${version}_${versionSub}.zip`;

	await replaceXml.main(Manifest, zipFilename);

	from = Manifest;
	to = `./dist/${manifestFileName}`;
	await fse.copy(from, to
	).then(
		answer => console.log(
			pc.yellow(pc.bold(`Copied "${from}" to "${to}".`))
		)
	);

	// ## Create zip file and detect checksum then.
	const zipFilePath = `./dist/${zipFilename}`;
	const zip = new (require('adm-zip'))();
	zip.addLocalFolder("package", false);
	await zip.writeZip(`${zipFilePath}`);
	console.log(pc.cyan(pc.bold(pc.bgRed(`./dist/${zipFilename} written.`))));

	const Digest = 'sha256'; //sha384, sha512
	const checksum = await helper.getChecksum(zipFilePath, Digest)
  .then(
		hash => {
			const tag = `<${Digest}>${hash}</${Digest}>`;
			console.log(pc.green(pc.bold(`Checksum tag is: ${tag}`)));
			return tag;
		}
	)
	.catch(error => {
		console.log(error);
		console.log(pc.red(pc.bold(
			`Error while checksum creation. I won't set one!`)));
		return '';
	});

	xmlFile = 'update.xml';
	await fse.copy(`./${xmlFile}`, `./dist/${xmlFile}`).then(
		answer => console.log(pc.yellow(pc.bold(
			`Copied "${xmlFile}" to ./dist.`)))
	);
	await replaceXml.main(`${__dirname}/dist/${xmlFile}`, zipFilename, checksum,
		thisPackages);

	xmlFile = 'changelog.xml';
	await fse.copy(`./${xmlFile}`, `./dist/${xmlFile}`).then(
		answer => console.log(pc.yellow(pc.bold(
			`Copied "${xmlFile}" to ./dist.`)))
	);
	await replaceXml.main(`${__dirname}/dist/${xmlFile}`, zipFilename, checksum,
		thisPackages);

	xmlFile = 'release.txt';
	await fse.copy(`./${xmlFile}`, `./dist/${xmlFile}`).then(
		answer => console.log(pc.yellow(pc.bold(
			`Copied "${xmlFile}" to ./dist.`)))
	);
	await replaceXml.main(`${__dirname}/dist/${xmlFile}`, zipFilename, checksum,
		thisPackages);

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
