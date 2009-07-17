//Main build script for Dojo
var buildTimerStart = (new Date()).getTime();

load("jslib/logger.js");
load("jslib/fileUtil.js");
load("jslib/buildUtil.js");
load("jslib/buildUtilXd.js");
load("jslib/i18nUtil.js");

//NOTE: See buildUtil.DojoBuildOptions for the list of build options.

//*****************************************************************************
//Convert arguments to keyword arguments.
var kwArgs = buildUtil.makeBuildOptions(arguments);

//Set logging level.
logger.level = kwArgs["log"];

//Execute the requested build actions
var action = kwArgs.action;
for(var i = 0; i < action.length; i ++){
	logger.logPrefix = action[i] + ": ";
	this[action[i]]();
	logger.logPrefix = "";
}

var buildTime = ((new Date().getTime() - buildTimerStart) / 1000);
logger.info("Build time: " + buildTime + " seconds");
//*****************************************************************************

//********* Start help ************
function help(){
	var buildOptionText = "";
	for(var param in buildUtil.DojoBuildOptions){
		buildOptionText += param + "=" + buildUtil.DojoBuildOptions[param].defaultValue + "\n"
			+ buildUtil.DojoBuildOptions[param].helpText + "\n\n";
	}

	var helpText = "To run the build, you must have Java 1.4.2 or later installed.\n"
		+ "To run a build run the following command from this directory:\n\n"
		+ "> java -jar ../shrinksafe/custom_rhino.jar build.js [name=value...]\n\n"
		+ "Here is an example of a typical release build:\n\n"
		+ "> java -jar ../shrinksafe/custom_rhino.jar build.js profile=base action=release\n\n"
		+ "The possible name=value build options are shown below with the defaults as their values:\n\n"
		+ buildOptionText;
	
	print(helpText);
}
//********* End help *********

//********* Start clean ************
function clean(){
	logger.info("Deleting: " + kwArgs.releaseDir);
	fileUtil.deleteFile(kwArgs.releaseDir);
}
//********* End clean *********

//********* Start release *********
function release(){
	logger.info("Using profile: " + kwArgs.profileFile);
	logger.info("Using version number: " + kwArgs.version + " for the release.");

	if(!kwArgs.buildLayers){
		clean();
	}

	var dependencies = kwArgs.profileProperties.dependencies;
	var prefixes = dependencies.prefixes;
	var lineSeparator = fileUtil.getLineSeparator();
	var copyrightText = fileUtil.readFile("copyright.txt");
	var buildNoticeText = fileUtil.readFile("build_notice.txt");
	
	//Find the dojo prefix path. Need it to process other module prefixes.
	var dojoPrefixPath = buildUtil.getDojoPrefixPath(prefixes);

	//Convert targeted build layers to an array.
	var buildLayers = null;
	if(kwArgs.buildLayers){
		//Make sure to copy over any "source" files for the layers be targeted by
		//buildLayers. Otherwise dependencies will not be calculated correctly.
		buildLayers = kwArgs.buildLayers.split(",");
	}

	//Get the list of module directories we need to process.
	//They will be in the dependencies.prefixes array.
	//Copy each prefix dir to the releases and
	//operate on that copy instead of modifying the source.
	for(var i = 0; i < prefixes.length; i++){
		var prefixName = prefixes[i][0];
		var prefixPath = prefixes[i][1];

		var finalPrefixPath = prefixPath;
		if(finalPrefixPath.indexOf(".") == 0 && prefixName != "dojo"){
			finalPrefixPath = dojoPrefixPath + "/" + prefixPath;
		}
		_copyToRelease(prefixName, finalPrefixPath, kwArgs, buildLayers);

		if(kwArgs.symbol){
			var releasePath = kwArgs.releaseDir + "/"  + prefixName.replace(/\./g, "/");
			buildUtil.insertSymbols(releasePath, kwArgs);
		}
	}

	//Fix all the prefix paths to be in the release directory.
	//Do this after the copy step above. If it is done as part
	//of that loop, then dojo path gets set first usually, and any prefixes
	//after it are wrong.
	for(var i = 0; i < prefixes.length; i++){
		prefixes[i][1] = kwArgs.releaseDir + "/"  + prefixes[i][0].replace(/\./g, "/");
	}

	//Make sure dojo is clear before trying to map dependencies.
	if(typeof dojo != "undefined"){
		dojo = undefined;
	}

	logger.trace("Building dojo.js and layer files");
	var result = buildUtil.makeDojoJs(buildUtil.loadDependencyList(kwArgs.profileProperties, kwArgs), kwArgs.version, kwArgs);

	//Save the build layers. The first layer is dojo.js.
	var defaultLegalText = copyrightText + buildNoticeText;
	var dojoReleaseDir = kwArgs.releaseDir + "/dojo/";
	var layerIgnoreString = "";
	var nlsIgnoreString = "";
	
	//Add an ending comma to the list to make matches easier.
	//Also make sure we normalize to unix path separators.
	if(kwArgs.buildLayers){
		kwArgs.buildLayers += ",";
		kwArgs.buildLayers = kwArgs.buildLayers.replace(/\\/g, "/");
	}
	for(var i = 0; i < result.length; i++){
		var currentLayer = result[i];
		var layerName = currentLayer.layerName;
		var layerLegalText = (currentLayer.copyrightFile ? fileUtil.readFile(currentLayer.copyrightFile) : defaultLegalText);
		var fileName = dojoReleaseDir + currentLayer.layerName;
		var fileContents = currentLayer.contents;
		
		//Build up string of files to ignore for the directory optimization step
		var ignoreName = layerName.replace(/\.\.\//g, "");
		var nameSegment = ignoreName.replace(/\.js$/, "");
		layerIgnoreString += (layerIgnoreString ? "|" : "") + buildUtil.regExpEscape(ignoreName) + "$";
		layerIgnoreString += "|" + buildUtil.regExpEscape(ignoreName + ".uncompressed.js") + "$";

		if(nameSegment.indexOf("/") != -1){
			nameSegment = nameSegment.substring(nameSegment.lastIndexOf("/") + 1, nameSegment.length);
		}
		nlsIgnoreString += (nlsIgnoreString ? "|" : "") + buildUtil.regExpEscape(nameSegment);

		//If only want to build certain layers, skip ones that do not match.
		if(kwArgs.buildLayers && kwArgs.buildLayers.indexOf(layerName + ",") == -1){
			continue;
		}

		//Burn in djConfig for dojo.js/xd.js if requested.
		if(kwArgs.scopeDjConfig && (layerName.match(/dojo\.xd\.js$/) || layerName.match(/dojo\.js$/))){
			fileContents = buildUtil.setScopeDjConfig(fileContents, kwArgs.scopeDjConfig);
		}

		//Burn in scope names for dojo.js/xd.js if requested.
		if(kwArgs.scopeMap && (layerName.match(/dojo\.xd\.js$/) || layerName.match(/dojo\.js$/))){
			fileContents = buildUtil.setScopeNames(fileContents, kwArgs.scopeMap);
		}

		//Burn in xd path for dojo if requested, and only do this in dojo.xd.js.
		if(layerName.match(/dojo\.xd\.js/) && kwArgs.xdDojoPath){
			fileContents = buildUtilXd.setXdDojoConfig(fileContents, kwArgs.xdDojoPath);
		}

		//Flatten resources
		fileContents = i18nUtil.flattenLayerFileBundles(fileName, fileContents, kwArgs);

		//Save uncompressed file.
		var uncompressedFileName = fileName + ".uncompressed.js";
		var uncompressedContents = layerLegalText + fileContents;
		if(layerName.match(/\.xd\.js$/) && !layerName.match(/dojo(\.xd)?\.js/)){
			uncompressedContents = buildUtilXd.makeXdContents(uncompressedContents, prefixes, kwArgs);
		}
		fileUtil.saveUtf8File(uncompressedFileName, uncompressedContents);

		//Intern strings if desired. Do this before compression, since, in the xd case,
		//"dojo" gets converted to a shortened name.
		if(kwArgs.internStrings){
			logger.info("Interning strings for file: " + fileName);
			var prefixes = dependencies["prefixes"] || [];
			var skiplist = dependencies["internSkipList"] || [];
			buildUtil.internTemplateStringsInFile(uncompressedFileName, dojoReleaseDir, prefixes, skiplist);

			//Load the file contents after string interning, to pick up interned strings.
			fileContents = fileUtil.readFile(uncompressedFileName);
		}else{
			fileContents = uncompressedContents;
		}

		//Save compressed file.
		logger.trace("Optimizing (" + kwArgs.layerOptimize + ") file: " + fileName);
		var compressedContents = buildUtil.optimizeJs(fileName, fileContents, layerLegalText, kwArgs.layerOptimize);
		fileUtil.saveUtf8File(fileName, compressedContents);

	}

	//Save the dependency lists to build.txt
	var buildText = "Files baked into this build:" + lineSeparator;
	for(var i = 0; i < result.length; i++){
		buildText += lineSeparator + result[i].layerName + ":" + lineSeparator;
		buildText += result[i].depList.join(lineSeparator) + lineSeparator;
	}
	fileUtil.saveFile(kwArgs.releaseDir + "/dojo/build.txt", buildText);
	logger.info(buildText);

	//Run string interning, xd file building, etc.. on the prefix dirs in the
	//release area.
	var layerIgnoreRegExp = new RegExp("(" + layerIgnoreString + ")");
	var nlsIgnoreRegExp = new RegExp("\\/nls\\/(" + nlsIgnoreString + ")_");

	for(var i = 0; i < prefixes.length; i++){
		var copyrightText = null;
		if(prefixes[i][2]){
			copyrightText = fileUtil.readFile(prefixes[i][2]);
		}

		//Optimize the release dirs, but only if we are not building just a layer.
		if(!kwArgs.buildLayers){
			_optimizeReleaseDirs(prefixes[i][0], prefixes[i][1], copyrightText, kwArgs, layerIgnoreRegExp, nlsIgnoreRegExp);
		}
	}

	//Copy over DOH if tests where copied.
	if(kwArgs.copyTests){
		copyRegExp = new RegExp(prefixName.replace(/\\/g, "/") + "/(?!tests)");
		fileUtil.copyDir("../doh", kwArgs.releaseDir + "/util/doh", /./);
	}

	logger.info("Build is in directory: " + kwArgs.releaseDir);
}
//********* End release *********

//********* Start _copyToRelease *********
function _copyToRelease(/*String*/prefixName, /*String*/prefixPath, /*Object*/kwArgs, /*Array?*/buildLayers){
	//summary: copies modules and supporting files from the prefix path to the release
	//directory. Also adds code guards to module resources.
	var prefixSlashName = prefixName.replace(/\./g, "/");
	var releasePath = kwArgs.releaseDir + "/"  + prefixSlashName;
	var copyRegExp = /./;
	
	//Use the copyRegExp to filter out tests if requested.
	if(!kwArgs.copyTests){
		copyRegExp = new RegExp(prefixName.replace(/\\/g, "/") + "/(?!tests)");
	}

	logger.info("Copying: " + prefixPath + " to: " + releasePath);
	var copiedFiles = fileUtil.copyDir(prefixPath, releasePath, copyRegExp, true);

	//Make sure to copy over any "source" files for the layers be targeted by
	//buildLayers. Otherwise dependencies will not be calculated correctly.
	if(buildLayers){
		for(i = 0; i < buildLayers.length; i++){		
			var relativeLayerPath = buildLayers[i].replace(/\.\.\//g, "");
			
			//See if relativeLayerPath has teh prefix slash name in it.
			//This means the layer is probably in this prefix dir (but no guarantee)
			//This is a bit hacky.
			if(relativeLayerPath.indexOf(prefixSlashName) == 0){
				
				//Remove the prefix part from the dir and add the prefix path to get a
				//full path.
				var layerPathSuffix = relativeLayerPath.replace(prefixSlashName, "");
				relativeLayerPath = prefixPath + layerPathSuffix;
				
				//If that source path exists, it means we need to copy over the source
				//layer file.
				if((new java.io.File(relativeLayerPath)).exists()){
					//Need to copy over from the source area.
					var destPath = releasePath + layerPathSuffix;
					fileUtil.copyFile(relativeLayerPath, destPath);
				}
			}
		}
	}

	//Put in code guards for each resource, to protect against redefinition of
	//code in the layered build cases. Do this here before the layers are built.
	if(copiedFiles){
		buildUtil.addGuards(copiedFiles);
	}
}
//********* End _copyToRelease *********

//********* Start _optimizeReleaseDirs *********
function _optimizeReleaseDirs(
	/*String*/prefixName, 
	/*String*/prefixPath,
	/*String*/copyrightText,
	/*Object*/kwArgs,
	/*RegExp*/layerIgnoreRegExp,
	/*RegExp*/nlsIgnoreRegExp){	
	//summary: runs intern strings, i18n bundle flattening and xdomain file generation
	//on the files in a release directory, if those options are enabled.
	var releasePath = kwArgs.releaseDir + "/"  + prefixName.replace(/\./g, "/");
	var prefixes = kwArgs.profileProperties.dependencies.prefixes;

	//Intern strings if desired.
	if(kwArgs.internStrings){
		logger.info("Interning strings for: " + releasePath);
		buildUtil.internTemplateStrings(kwArgs.profileProperties.dependencies, releasePath, layerIgnoreRegExp);
	}

	//Process build conditionals in non-layer module files.
	buildUtil.processConditionalsForDir(releasePath, layerIgnoreRegExp, kwArgs);

	//Flatten bundles inside the directory
	i18nUtil.flattenDirBundles(prefixName, prefixPath, kwArgs, nlsIgnoreRegExp);	
	
	if(kwArgs.loader == "xdomain"){
		buildUtilXd.xdgen(prefixName, prefixPath, prefixes, layerIgnoreRegExp, kwArgs);
	}

	//FIXME: call stripComments. Maybe rename, inline with optimize? need build options too.
	if(kwArgs.optimize){
		buildUtil.stripComments(releasePath, layerIgnoreRegExp, copyrightText, kwArgs.optimize);
	}
	
	if(kwArgs.cssOptimize){
		buildUtil.optimizeCss(releasePath, kwArgs.cssOptimize, kwArgs.cssImportIgnore);
	}
}
//********* End _optimizeReleaseDirs *********
