<?php

namespace App\Livewire;

use App\Models\Ticket;
use Livewire\Component;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Concerns\InteractsWithForms;

class TicketCommentForm extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    public Ticket $ticket;
    public $newComment = '';

    protected function customAttachFilesAction(): Action
    {
        return Action::make('attachFiles')
            ->label(__('filament-forms::components.rich_editor.actions.attach_files.label'))
            ->modalHeading(__('filament-forms::components.rich_editor.actions.attach_files.modal.heading'))
            ->modalWidth(Width::Large)
            ->fillForm(fn (array $arguments): array => [
                'alt' => $arguments['alt'] ?? null,
            ])
            ->schema(fn (array $arguments, RichEditor $component): array => [
                FileUpload::make('file')
                    ->label(filled($arguments['src'] ?? null)
                        ? __('filament-forms::components.rich_editor.actions.attach_files.modal.form.file.label.existing')
                        : __('filament-forms::components.rich_editor.actions.attach_files.modal.form.file.label.new'))
                    ->acceptedFileTypes($component->getFileAttachmentsAcceptedFileTypes())
                    ->maxSize($component->getFileAttachmentsMaxSize())
                    ->storeFiles(false)
                    ->required(blank($arguments['src'] ?? null))
                    ->hiddenLabel(blank($arguments['src'] ?? null)),
                TextInput::make('alt')
                    ->label(filled($arguments['src'] ?? null)
                        ? __('filament-forms::components.rich_editor.actions.attach_files.modal.form.alt.label.existing')
                        : __('filament-forms::components.rich_editor.actions.attach_files.modal.form.alt.label.new'))
                    ->maxLength(1000),
            ])
            ->action(function (array $arguments, array $data, RichEditor $component, Component $livewire): void {
                if ($data['file'] ?? null) {
                    $file = $data['file'];
                    $isImage = Str::startsWith($file->getMimeType(), 'image/');
                    $fileName = $file->getClientOriginalName();

                    // Store file permanently to public disk
                    $directory = 'attachments/comments';
                    $storedPath = $file->store($directory, 'public');
                    $src = asset('storage/' . $storedPath);
                    $id = (string) Str::orderedUuid();
                }

                if (filled($arguments['src'] ?? null)) {
                    if ($arguments['editorSelection']['type'] !== 'node') {
                        $arguments['editorSelection']['type'] = 'node';
                        $arguments['editorSelection']['anchor']--;
                        unset($arguments['editorSelection']['head']);
                    }

                    $id ??= $arguments['id'] ?? null;
                    $src ??= $arguments['src'];

                    $component->runCommands(
                        [
                            EditorCommand::make('updateAttributes', arguments: [
                                'image',
                                [
                                    'alt' => $data['alt'] ?? null,
                                    'id' => $id,
                                    'src' => $src,
                                ],
                            ]),
                        ],
                        editorSelection: $arguments['editorSelection'],
                    );

                    return;
                }

                if (blank($id ?? null) || blank($src ?? null)) {
                    return;
                }

                // For image files, insert as image node (default behavior)
                if ($isImage ?? true) {
                    $component->runCommands(
                        [
                            EditorCommand::make('insertContent', arguments: [[
                                'type' => 'image',
                                'attrs' => [
                                    'alt' => $data['alt'] ?? null,
                                    'id' => $id,
                                    'src' => $src,
                                ],
                            ]]),
                        ],
                        editorSelection: $arguments['editorSelection'],
                    );
                } else {
                    // For non-image files (PDF, etc.), insert as a clickable link
                    $label = $data['alt'] ?? ($fileName ?? 'Lampiran');
                    $component->runCommands(
                        [
                            EditorCommand::make('insertContent', arguments: [
                                '<a href="' . e($src) . '" target="_blank" rel="noopener noreferrer">📎 ' . e($label) . '</a> ',
                            ]),
                        ],
                        editorSelection: $arguments['editorSelection'],
                    );
                }
            });
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                RichEditor::make('newComment')
                    ->label('Add a Comment')
                    ->placeholder('Write your comment here...')
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('attachments/comments')
                    ->fileAttachmentsVisibility('public')
                    ->fileAttachmentsAcceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                        'image/webp',
                        'application/pdf',
                    ])
                    ->registerActions([
                        $this->customAttachFilesAction(),
                    ])
                    ->toolbarButtons([
                        'attachFiles',
                        'blockquote',
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                    ])
                    ->required()
                    ->extraInputAttributes(['style' => 'min-height: 10rem;']),
            ]);
    }

    public function addComment()
    {
        $data = $this->form->getState();

        $this->ticket->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $data['newComment']
        ]);

        auth()->user()->notifications()
            ->where('data->ticket_id', $this->ticket->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        Notification::make()
            ->title('Comment added successfully')
            ->success()
            ->send();

        $this->form->fill();

        $this->dispatch('comment-added');
    }

    public function render()
    {
        return view('livewire.ticket-comment-form');
    }
}
