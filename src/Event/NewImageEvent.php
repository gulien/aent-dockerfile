<?php

namespace TheAentMachine\AentDockerfile\Event;

use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\StringsException;
use Symfony\Component\Filesystem\Filesystem;
use TheAentMachine\Aent\Context\Context;
use TheAentMachine\Aent\Event\Builder\AbstractNewImageEvent;
use TheAentMachine\Aent\Payload\Builder\NewImageReplyPayload;
use TheAentMachine\Aenthill\Pheromone;
use TheAentMachine\Exception\MissingEnvironmentVariableException;
use TheAentMachine\Service\Service;
use function \Safe\sprintf;
use function \Safe\chown;
use function \Safe\chgrp;

final class NewImageEvent extends AbstractNewImageEvent
{
    /** @var Context */
    private $context;

    /**
     * @param Service $service
     * @throws StringsException
     */
    protected function before(Service $service): void
    {
        $this->context = Context::fromMetadata();
        $welcomeMessage = sprintf(
            "\n👋 Hello! I'm the aent <info>Dockerfile</info> and I'm going to create a Dockerfile for your service <info>%s</info>!",
            $service->getServiceName()
        );
        $this->output->writeln($welcomeMessage);
    }

    /**
     * @param Service $service
     * @return NewImageReplyPayload
     * @throws StringsException
     * @throws FilesystemException
     * @throws MissingEnvironmentVariableException
     */
    protected function process(Service $service): NewImageReplyPayload
    {
        $commands = \implode(PHP_EOL, $service->getDockerfileCommands()) . PHP_EOL;
        $dockerfileName = sprintf("Dockerfile.%s.%s", $this->context->getName(), $service->getServiceName());
        $dockerfilePath = Pheromone::getContainerProjectDirectory() . "/$dockerfileName";
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($dockerfilePath, $commands);
        $dirInfo = new \SplFileInfo(\dirname($dockerfilePath));
        chown($dockerfilePath, $dirInfo->getOwner());
        chgrp($dockerfilePath, $dirInfo->getGroup());
        return new NewImageReplyPayload($dockerfileName);
    }

    /**
     * @param Service $service
     * @param NewImageReplyPayload $payload
     * @throws StringsException
     */
    protected function after(Service $service, NewImageReplyPayload $payload): void
    {
        $afterMessage = sprintf(
            "\nI've created <info>%s</info> for your service <info>%s</info>.",
            $payload->getDockerfileName(),
            $service->getServiceName()
        );
        $this->output->writeln($afterMessage);
    }
}
