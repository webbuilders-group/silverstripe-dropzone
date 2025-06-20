<?php
namespace UncleCheese\Dropzone;

use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Delete all files being tracked that weren't saved against anything.
 *
 * WARNING: You must call Form::saveInto or 'FileAttachmentFieldTrack::untrack' against IDs on custom-built forms or you
 *          -will- remove files accidentally with this task.
 *
 * @package unclecheese/silverstripe-dropzone
 */
class FileAttachmentFieldCleanTask extends BuildTask
{
    protected static string $commandName = 'dropzone-clean';

    protected string $title = "File Attachment Field - Clear all tracked files that are older than 1 hour";

    protected static string $description = 'Delete files uploaded via FileAttachmentField that aren\'t attached to anything.';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $files = FileAttachmentFieldTrack::get()->filter(['Created:LessThanOrEqual' => date('Y-m-d H:i:s', time() - 3600)]);
        $files = $files->toArray();
        if ($files) {
            foreach ($files as $trackRecord) {
                $file = $trackRecord->File();
                if ($file->exists()) {
                    DB::alteration_message('Remove File #' . $file->ID . ' from "' . $trackRecord->ControllerClass . '" on ' . $trackRecord->RecordClass . ' #' . $trackRecord->RecordID, 'error');
                    $file->delete();
                } else {
                    DB::alteration_message('Untrack missing File #' . $file->ID . ' from "' . $trackRecord->ControllerClass . '" on ' . $trackRecord->RecordClass . ' #' . $trackRecord->RecordID, 'error');
                }

                $trackRecord->delete();
            }
        } else {
            DB::alteration_message('No tracked files to remove.');
        }

        return Command::SUCCESS;
    }
}
