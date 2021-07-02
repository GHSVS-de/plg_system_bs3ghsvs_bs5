const fse = require('fs-extra');
const util = require("util");
const rimRaf = util.promisify(require("rimraf"));
const chalk = require('chalk');
const exec = util.promisify(require('child_process').exec);
const path = require('path');

const Manifest = "./package/bs3ghsvs.xml";

const {
	author,
	creationDate,
	copyright,
	filename,
	name,
	nameReal,
	version,
	licenseLong,
	minimumPhp,
	maximumPhp,
	minimumJoomla,
	maximumJoomla,
	allowDowngrades,
} = require("./package.json");

// Joomla media folder (target workdir) inside this project. For copy-to actions.
const pathMedia = `./media`;

const program = require('commander');

const RootPath = process.cwd();

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
//console.log(Program.svg);
//process.exit(0);

async function buildOverview()
{
	const { stdout, stderr } = await exec('php bin/icons-html.php');

	if (stderr)
	{
		console.error(`error during icons-html.php: ${stderr}`);
	}
	console.log(`${stdout}`);
}

async function cleanOut (cleanOuts) {
	for (const file of cleanOuts)
	{
		await rimRaf(file).then(
			answer => console.log(chalk.redBright(`rimrafed: ${file}.`))
		);
	}
}

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

	await cleanOut(cleanOuts);

//process.exit(0);
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
		answer => console.log(chalk.yellowBright(`Copied ${source} to ${target}.`))
	);

	await fse.copy(
		"./node_modules/jquery/dist",
		`${pathMedia}/js/jquery`
		// ,
		// {overwrite:false, errorOnExist:true}
	);

	await fse.copy(
		"./node_modules/jquery-migrate/dist",
		`${pathMedia}/js/jquery-migrate`
		// ,
		// {overwrite:false, errorOnExist:true}
	);

	await fse.copy(
		"./package-lock.json",
		"./package/versions-installed/npm_package-lock.json"
	).then(
		answer => console.log(chalk.yellowBright(`Copied ./package-lock.json.`))
	);

	await fse.copy(
		"./_composer/vendor/composer/installed.json",
		"./package/versions-installed/composer_installed.json"
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

	const sourceInfos = JSON.parse(fse.readFileSync(`${__dirname}/node_modules/bootstrap/package.json`).toString());

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

	await console.log(chalk.redBright(`Be patient! Composer copy actions!`));
	// Copy and create new work dir.
	await fse.copy("./_composer/vendor", "./package/vendor"
	).then(
		answer => console.log(chalk.yellowBright(`Copied _composer/vendor to ./package.`))
	);

	// Create new dist dir.
	if (!(await fse.exists("./dist")))
	{
    	await fse.mkdir("./dist"
		).then(
			answer => console.log(chalk.yellowBright(`Created ./dist.`))
		);
	}

	let xml = await fse.readFile(Manifest, { encoding: "utf8" });
	xml = xml.replace(/{{name}}/g, nameReal);
	xml = xml.replace(/{{nameUpper}}/g, nameReal.toUpperCase());
	xml = xml.replace(/{{authorName}}/g, author.name);
	xml = xml.replace(/{{creationDate}}/g, creationDate);
	xml = xml.replace(/{{copyright}}/g, copyright);
	xml = xml.replace(/{{licenseLong}}/g, licenseLong);
	xml = xml.replace(/{{authorUrl}}/g, author.url);
	xml = xml.replace(/{{version}}/g, version);
	xml = xml.replace(/{{minimumPhp}}/g, minimumPhp);
	xml = xml.replace(/{{maximumPhp}}/g, maximumPhp);
	xml = xml.replace(/{{minimumJoomla}}/g, minimumJoomla);
	xml = xml.replace(/{{maximumJoomla}}/g, maximumJoomla);
	xml = xml.replace(/{{allowDowngrades}}/g, allowDowngrades);
	xml = xml.replace(/{{filename}}/g, filename);

	await fse.writeFile(Manifest, xml, { encoding: "utf8" }
	).then(
		answer => console.log(chalk.yellowBright(`Replaced entries in ${Manifest}.`))
	);

	// Pack it.
	const zip = new (require("adm-zip"))();
	const zipFilename = `dist/${name}-${version}_${sourceInfos.version}.zip`;
	zip.addLocalFolder("package", false);
	zip.writeZip(`${zipFilename}`);
	console.log(chalk.greenBright(`./dist/${zipFilename} written.`));

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

	await cleanOut(cleanOuts).then(
		answer => console.log(chalk.yellowBright(`Finish.`))
	);
})();
