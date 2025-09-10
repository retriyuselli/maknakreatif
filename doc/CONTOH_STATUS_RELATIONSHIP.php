<?php

// CONTOH IMPLEMENTASI YANG BENAR UNTUK USERRESOURCE

// 1. Untuk Select Field dengan getOptionLabelFromRecordUsing:
Forms\Components\Select::make('user_id')
    ->label('User')
    ->relationship('user', 'name', function (Builder $query) {
        return $query->with('status'); // Eager load relationship
    })
    ->getOptionLabelFromRecordUsing(function (User $record): string {
        // CARA YANG BENAR:
        $statusName = $record->status?->status_name ?? 'No Status';
        return "{$record->name} ({$statusName})";
    }),

// 2. Untuk Table Column:
Tables\Columns\TextColumn::make('status.status_name')
    ->label('Status Jabatan')
    ->badge()
    ->searchable(),

// 3. Untuk Custom Format di Table:
Tables\Columns\TextColumn::make('status_info')
    ->label('Status Info')
    ->formatStateUsing(function ($record): string {
        // CARA YANG BENAR:
        $statusName = $record->status?->status_name ?? 'No Status';
        return $statusName;
    }),

// 4. Untuk Action dengan Status Check:
Tables\Actions\Action::make('update_status')
    ->action(function ($record): void {
        // CARA YANG BENAR:
        $currentStatus = $record->status?->status_name ?? 'Unknown';
        // logic here...
    }),

// 5. Untuk Filter berdasarkan Status:
Tables\Filters\SelectFilter::make('status')
    ->label('Status Jabatan')
    ->relationship('status', 'status_name')
    ->preload(),

// PENTING: 
// - status_id = foreign key (integer)
// - status = relationship (object)
// - status->status_name = nama status dari relationship

?>
