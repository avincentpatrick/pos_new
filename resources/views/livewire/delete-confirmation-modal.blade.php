<div>
    <x-dialog-modal wire:model="show">
        <x-slot name="title">
            Delete Confirmation
        </x-slot>

        <x-slot name="content">
            <p>Please enter an administrator's password to confirm deletion.</p>
            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" type="password" class="mt-1 block w-full" wire:model.defer="password" />
                <x-input-error for="password" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('show', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <x-danger-button class="ms-2" wire:click="confirmDelete" wire:loading.attr="disabled">
                Delete
            </x-danger-button>
        </x-slot>
    </x-dialog-modal>
</div>
