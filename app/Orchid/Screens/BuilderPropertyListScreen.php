<?php

namespace App\Orchid\Screens;

use App\Models\Property;
use App\Models\Project;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\Link; // <--- Changed from Button/ModalToggle
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Upload;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuilderPropertyListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'properties' => Property::whereHas('project', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->with('project')
            ->latest()
            ->paginate(10)
        ];
    }

    public function permission(): ?iterable
    {
        return [
            'platform.builder.properties',
        ];
    }

    public function name(): ?string
    {
        return 'Properties (Units)';
    }

    public function description(): ?string
    {
        return 'Manage individual unit inventory, pricing, and availability.';
    }

    public function commandBar(): iterable
    {
        return [
            // Keep Create as Modal for quick adding
            ModalToggle::make('Add Unit')
                ->modal('createPropertyModal')
                ->method('createProperty')
                ->icon('plus')
                ->type(Color::SUCCESS),
        ];
    }

    public function layout(): iterable
    {
        return [
            // 1. TABLE
            Layout::table('properties', [
                TD::make('title', 'Unit Title')
                    ->sort()
                    ->render(fn (Property $property) => "<strong>{$property->title}</strong>"),

                TD::make('project.name', 'Project')
                    ->render(fn (Property $property) => $property->project->name ?? '-'),

                TD::make('configuration', 'Config')->sort(),

                TD::make('price', 'Price')
                    ->sort()
                    ->render(fn (Property $property) => '₹ ' . number_format($property->price)),

                TD::make('status', 'Status')
                    ->render(fn (Property $property) => match ($property->status) {
                        'Available' => "<span class='text-success'>● {$property->status}</span>",
                        'Reserved' => "<span class='text-warning'>● {$property->status}</span>",
                        'Sold' => "<span class='text-danger'>● {$property->status}</span>",
                        default => "<span class='text-muted'>● {$property->status}</span>",
                    }),

                TD::make('Actions')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn (Property $property) => Link::make('Edit')
                        ->route('platform.builder.properties.edit', $property->id) // Points to new page
                        ->icon('pencil')
                        ->type(Color::LIGHT)),
            ]),

            // 2. CREATE MODAL (Kept for quick add)
            Layout::modal('createPropertyModal', Layout::rows([
                Relation::make('property.project_id')
                    ->title('Select Project')
                    ->fromModel(Project::class, 'name')
                    ->applyScope('byBuilder')
                    ->applyScope('verified')
                    ->required(),

                Input::make('property.title')->title('Unit Title')->required(),
                
                Select::make('property.configuration')
                    ->title('Configuration')
                    ->options(['1BHK'=>'1 BHK', '2BHK'=>'2 BHK', '3BHK'=>'3 BHK', '4BHK+'=>'4 BHK+', 'Villa'=>'Villa', 'Plot'=>'Plot'])
                    ->required(),

                Input::make('property.area_sqft')->title('Area (sqft)')->type('number')->required(),
                Input::make('property.price')->title('Price (₹)')->type('number')->required(),

                Upload::make('property.attachment')
                    ->title('Property Images')
                    ->groups('photos')
                    ->maxFiles(5)
                    ->acceptedFiles('image/*'),

            ]))->title('Add New Unit')->applyButton('Create'),
        ];
    }

    /**
     * LOGIC: Create New Property
     */
    public function createProperty(Request $request)
    {
        $data = $request->validate([
            'property.project_id' => 'required|exists:projects,id',
            'property.title' => 'required|string',
            'property.configuration' => 'required|string',
            'property.area_sqft' => 'required|numeric',
            'property.price' => 'required|numeric',
            'property.attachment' => 'array', 
        ])['property'];

        $data['status'] = 'Available';
        $property = Property::create($data);

        $property->attachment()->sync($request->input('property.attachment', []));

        Toast::info('Unit created successfully.');
    }
}