<?php

namespace App\Orchid\Screens;

use App\Models\Property;
use App\Models\Project;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Upload;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;
use Illuminate\Support\Facades\Auth;

class BuilderPropertyEditScreen extends Screen
{
    public $property;

    /**
     * Fetch data. Orchid automatically finds the Property from the URL.
     */
    public function query(Property $property): iterable
    {
        // Security: Ensure the builder owns the project this unit belongs to
        if ($property->project->user_id !== Auth::id()) {
            abort(403);
        }

        // Load images so they appear in the upload field
        $property->load('attachment');

        return [
            'property' => $property
        ];
    }

    public function name(): ?string
    {
        return 'Edit Unit: ' . $this->property->title;
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Save Changes')
                ->icon('check')
                ->method('save')
                ->type(Color::PRIMARY),

            Button::make('Delete Unit')
                ->icon('trash')
                ->method('remove')
                ->confirm('Are you sure you want to delete this unit permanently?')
                ->type(Color::DANGER),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Relation::make('property.project_id')
                    ->title('Project')
                    ->fromModel(Project::class, 'name')
                    ->applyScope('byBuilder')
                    ->required(),

                Input::make('property.title')
                    ->title('Unit Title')
                    ->required(),

                Select::make('property.configuration')
                    ->title('Configuration')
                    ->options([
                        '1BHK' => '1 BHK', '2BHK' => '2 BHK', '3BHK' => '3 BHK',
                        '4BHK+' => '4 BHK+', 'Villa' => 'Villa', 'Plot' => 'Plot',
                    ])
                    ->required(),

                Select::make('property.status')
                    ->title('Status')
                    ->options(['Available' => 'Available', 'Reserved' => 'Reserved', 'Sold' => 'Sold'])
                    ->required(),

                Input::make('property.area_sqft')->title('Area (sqft)')->type('number')->required(),
                Input::make('property.price')->title('Price (₹)')->type('number')->required(),

                Upload::make('property.attachment')
                    ->title('Property Images')
                    ->groups('photos')
                    ->maxFiles(5)
                    ->acceptedFiles('image/*'),
            ])
        ];
    }

    public function save(Property $property, Request $request)
    {
        if ($property->project->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'property.project_id' => 'required|exists:projects,id',
            'property.title' => 'required|string',
            'property.configuration' => 'required|string',
            'property.status' => 'required|string',
            'property.area_sqft' => 'required|numeric',
            'property.price' => 'required|numeric',
            'property.attachment' => 'array',
        ])['property'];

        $property->update($data);

        // Sync Images
        $property->attachment()->sync($request->input('property.attachment', []));

        Toast::info('Unit updated successfully.');
        return redirect()->route('platform.builder.properties');
    }

    public function remove(Property $property)
    {
        if ($property->project->user_id !== Auth::id()) {
            abort(403);
        }

        $property->attachment()->delete();
        $property->delete();

        Toast::info('Unit deleted successfully.');
        return redirect()->route('platform.builder.properties');
    }
}