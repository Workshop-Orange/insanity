<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    /**
     * Deploy an insanity sanity deployment
     *
     * @param $jsonDeployment The JSON Deployment Description File
     */
    public function sanityDeploy($jsonDeployment)
    {
	print "$jsonDeployment \n";

        if(!file_exists($jsonDeployment) || ! is_file($jsonDeployment))
        {
            $this->say('Deployment File Error: ' . $jsonDeployment);
            return new Robo\ResultData(255, 'Deployment File Error: ' . $jsonDeployment);
        }

        $baseDir = "deploys";
        if(!$deployment = $this->_prepareSanityDeploy($jsonDeployment, $baseDir)) {
            return new Robo\ResultData(254, 'Deployment File Format Error: ' . $jsonDeployment);
        }

        $sanityAuthToken = $deployment['sanityAuthToken'];
        $sanityRepo = $deployment['sanityRepo'];

        chdir($baseDir);
        chdir($sanityRepo);

        if(! $this->_exec($sanityAuthToken . ' sanity install')->wasSuccessful()) {
            $this->say('Deployment Sanity Install Error: ' . $jsonDeployment);
            return new Robo\ResultData(253, 'Deployment Sanity Install Error: ' . $jsonDeployment);
        }

        if($deployment['deployment']['create_dataset']) {
            if(!$this->_exec($sanityAuthToken . ' sanity dataset create ' . $deployment['deployment']['sanity_dataset'] . ' --visibility private')
                ->wasSuccessful())
                {
                    $this->say('Deployment Sanity Create Dataset Error: ' . $jsonDeployment);
                    return new Robo\ResultData(252, 'Deployment Sanity Create Dataset Error: ' . $jsonDeployment);
                }
        }

        if(!$this->_exec($sanityAuthToken . ' sanity deploy')->wasSuccessful())
        {
            $this->say('Deployment Sanity Deploy Error: ' . $jsonDeployment);
            return new Robo\ResultData(252, 'Deployment Sanity Deploy Error: ' . $jsonDeployment);
        }

        if(!$this->_exec($sanityAuthToken . ' sanity graphql deploy --no-playground --force')->wasSuccessful())
        {
            $this->say('Deployment Sanity GraphQL Deploy Error: ' . $jsonDeployment);
            return new Robo\ResultData(252, 'Deployment Sanity GraphQL Deploy Error: ' . $jsonDeployment);
        }

        return new Robo\ResultData(0, 'Deployment Complete: ' . $jsonDeployment);
    }

    private function _prepareSanityDeploy($jsonDeployment, $baseDir)
    {
        $deployment = json_decode(file_get_contents($jsonDeployment),  TRUE);
        $deployment['baseDir'] = $baseDir;

        if( empty($deployment['metadata'])
            || empty($deployment['deployment'])
            || empty($deployment['mainRepo'])
        ) {
            $this->say('Deployment File Format Error: ' . $jsonDeployment);
            return FALSE;
        }

        $this->say("Deployment File: " . $jsonDeployment);
        $this->say("Base Dir: " . $baseDir);

        $collection = $this->collectionBuilder();
        $wrkPath =  $collection->taskWorkDir($baseDir)->cwd(TRUE)->getPath();
        $sanityRepo = "$wrkPath/sanity-repo";
        $this->say("Work Dir: " . $wrkPath);

        $collection->taskFilesystemStack()
                ->mkdir("$wrkPath/log")
                ->touch("$wrkPath/log/error.txt");

        $collection->taskGitStack()
                ->cloneRepo($deployment['mainRepo']['git'], $sanityRepo, $deployment['mainRepo']['branch']);

        $collection->run();

        $cdir = getcwd();
        chdir($baseDir);
        chdir($sanityRepo);
        $deployment['sanityRepo'] = $sanityRepo;

        $collection = $this->collectionBuilder();

        $collection->taskGitStack()
            ->checkout($deployment['mainRepo']['branch'])
            ->run();

        $sanityBaseConfig = json_decode(file_get_contents("sanity-base.json"), TRUE);
        $sanityBaseConfig['api']['projectId'] = $deployment['deployment']['sanity_project_id'];
        $sanityBaseConfig['api']['dataset'] = $deployment['deployment']['sanity_dataset'];
        $sanityBaseConfig['project']['name'] = $deployment['deployment']['title'];

        $deployment['sanityAuthToken'] = 'SANITY_AUTH_TOKEN="' . $deployment['deployment']['sanity_api_token'] . '"';

        file_put_contents("sanity.json", json_encode($sanityBaseConfig));

        chdir($cdir);
        return $deployment;
    }
}
