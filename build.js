const fse = require('fs-extra');
const util = require("util");
const rimRaf = util.promisify(require("rimraf"));
const chalk = require('chalk');
const exec = util.promisify(require('child_process').exec);
const path = require('path');
const replaceXml = require('./build/replaceXml.js');
const helper = require('./build/helper.js');
const program = require('commander');

const {
	filename,
	name,
	version,
} = require("./package.json");

const manifestFileName = `${filename}.xml`;
const Manifest = `${__dirname}/package/${manifestFileName}`;
const pathMedia = `./media`;
let versionSub = '';

async function buildOverview()
{
	const { stdout, stderr } = await exec('php bin/icons-html.php');

	if (stderr)
	{
		console.error(`error during icons-html.php: ${stderr}`);
	}
	console.log(`${stdout}`);
}

program
  .version(version)
  .option('--svg', 'Additionally prepare svgs in /svg-icons/ for Joomla usage')
  .on('--help', () => {
    // eslint-disable-next-line no-console
    console.log(`Version: ${version}`);
    process.exit(0);
  })
  .parse(process.argv);

const Program = program.opts();

(async function exec()
{
	// This is for SCSS compilation for tpl_bs4ghsvs.
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

	versionSub = await helper.findVersionSub (
		path.join(`${__dirname}`, `node_modules`, `bootstrap`, `package.json`),
		'Bootstrap');

	console.log(chalk.magentaBright(`versionSub identified as: "${versionSub}"`));

// ### Prepare /media/.

	let copyThis = [
		'css', 'scss', 'webfonts', 'LICENSE.txt', 'package.json'
	];

	for (const file of copyThis)
	{
		let source = `./node_modules/@fortawesome/fontawesome-free/${file}`;
		let target = `${pathMedia}/fontawesome-free/${file}`;
		await fse.copy(source, target
		).then(
			answer => console.log(chalk.yellowBright(`Copied fontawesome-free/${path.basename(target)}.`))
		);
	}

	copyThis = ['js', 'css'];

	for (const file of copyThis)
	{
		let source = `./node_modules/bootstrap/dist/${file}`;
		let target = `${pathMedia}/${file}/bootstrap`;
		await fse.copy(source, target
		).then(
			answer => console.log(chalk.yellowBright(`Copied bootstrap/${path.basename(source)}.`))
		);
	}

	source = "./node_modules/bootstrap/js/dist";
	target = `${pathMedia}/js/bootstrap/plugins`;

	await fse.copy(source, target
	).then(
		answer => console.log(chalk.yellowBright(`Copied ${source} to ${target}.`))
	);

	source = "./node_modules/bootstrap/scss";
	target = `${pathMedia}/scss/bootstrap`;

	await fse.copy(source, target
	).then(
		answer => console.log(chalk.yellowBright(`Copied "${source}" to "${target}".`))
	);

	// This is for SCSS compilation for tpl_bs4ghsvs.
	await fse.copy(target, externalScssFolder
	).then(
		answer => console.log(chalk.yellowBright(`Copied "${target}" to
			"${externalScssFolder}".`))
	);

	await fse.copy(
		`./node_modules/jquery/dist`,
		`${pathMedia}/js/jquery`
		// ,
		// {overwrite:false, errorOnExist:true}
	);

	await fse.copy(
		`./node_modules/jquery-migrate/dist`,
		`${pathMedia}/js/jquery-migrate`
		// ,
		// {overwrite:false, errorOnExist:true}
	);

	if (Program.svg === true)
	{
		console.log(chalk.greenBright(`Program.svg: YES`));
		console.log(chalk.redBright(`Be very very patient! Preparing svg files`));

		await rimRaf(`${pathMedia}/svgs`);

  	await fse.copy(
			"./node_modules/@fortawesome/fontawesome-free/svgs",
			`${pathMedia}/svgs`
		);

  	await fse.copy(
			"./node_modules/bootstrap-icons/icons",
			`${pathMedia}/svgs/bi`
		);

		const buildSvgs = require('./build/build-svgs.js');
		await buildSvgs.main();
	}

	await console.log(chalk.redBright(`Be patient! Copy actions!`));

	// Copy and create new work dir.
	await fse.copy(`${pathMedia}`, "./package/media"
	).then(
		answer => console.log(chalk.yellowBright(`Copied ${pathMedia} to ./package/media.`))
	);

	// Copy and create new work dir.
	await fse.copy("./src", "./package"
	).then(
		answer => console.log(chalk.yellowBright(`Copied ./src to ./package.`))
	);

	if (!(await fse.exists("./dist")))
	{
    	await fse.mkdir("./dist"
		).then(
			answer => console.log(chalk.yellowBright(`Created "./dist".`))
		);
  }

	const zipFilename = `${name}-${version}_${versionSub}.zip`;

	await replaceXml.main(Manifest, zipFilename);
	await fse.copy(`${Manifest}`, `./dist/${manifestFileName}`).then(
		answer => console.log(chalk.yellowBright(
			`Copied "${manifestFileName}" to "./dist".`))
	);

	// Create zip file and detect checksum then.
	const zipFilePath = `./dist/${zipFilename}`;

	const zip = new (require('adm-zip'))();
	zip.addLocalFolder("package", false);
	await zip.writeZip(`${zipFilePath}`);
	console.log(chalk.cyanBright(chalk.bgRed(
		`./dist/${zipFilename} written.`)));

	const Digest = 'sha256'; //sha384, sha512
	const checksum = await helper.getChecksum(zipFilePath, Digest)
  .then(
		hash => {
			const tag = `<${Digest}>${hash}</${Digest}>`;
			console.log(chalk.greenBright(`Checksum tag is: ${tag}`));
			return tag;
		}
	)
	.catch(error => {
		console.log(error);
		console.log(chalk.redBright(`Error while checksum creation. I won't set one!`));
		return '';
	});

	let xmlFile = 'update.xml';
	await fse.copy(`./${xmlFile}`, `./dist/${xmlFile}`).then(
		answer => console.log(chalk.yellowBright(
			`Copied "${xmlFile}" to ./dist.`))
	);
	await replaceXml.main(`${__dirname}/dist/${xmlFile}`, zipFilename, checksum);

	xmlFile = 'changelog.xml';
	await fse.copy(`./${xmlFile}`, `./dist/${xmlFile}`).then(
		answer => console.log(chalk.yellowBright(
			`Copied ${xmlFile} to ./dist.`))
	);
	await replaceXml.main(`${__dirname}/dist/${xmlFile}`, zipFilename, checksum);

	xmlFile = 'release.txt';
	await fse.copy(`./${xmlFile}`, `./dist/${xmlFile}`).then(
		answer => console.log(chalk.yellowBright(
			`Copied ${xmlFile} to ./dist.`))
	);
	await replaceXml.main(`${__dirname}/dist/${xmlFile}`, zipFilename, checksum);

	await buildOverview();

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
		answer => console.log(chalk.cyanBright(chalk.bgRed(
			`Finished. Good bye!`)))
	);
})();
