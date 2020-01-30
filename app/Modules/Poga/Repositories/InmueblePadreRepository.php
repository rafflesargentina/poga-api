<?php

namespace Raffles\Modules\Poga\Repositories;

use Raffles\Modules\Poga\Models\{ InmueblePadre, User };

use Caffeinated\Repository\Repositories\EloquentRepository;
use Illuminate\Http\Request;

class InmueblePadreRepository extends EloquentRepository
{
    /**
     * @var Model
     */
    public $model = InmueblePadre::class;

    /**
     * @var array
     */
    public $tag = ['InmueblePadre'];

    /**
     * Find: Disponibles para Administrar.
     *
     * @return array
     */
    public function findDisponiblesAdministrar()
    {
        $items = $this->with('unidades')
            ->whereHas(
                'idInmueble', function ($query) {
                    return $query->doesntHave('idAdministradorReferente'); 
                }
            )
            ->orWhereHas(
                'unidades', function ($query) {
                    return $query->doesntHave('idInmueble.idAdministradorReferente'); 
                }
            )->get();

        return $this->map($items);
    }


    public function misInmuebles(Request $request)
    {
	$user = $request->user();

        $rPagare = new PagareRepository;

	$items = $this->filter()->sort()
	    ->with(['idInmueble.pagares' => function($query) {
		    return $query->where('enum_estado', 'PAGADO')->whereIn('enum_clasificacion_pagare', ['RENTA','OTRO']);
	    }])
	    ->whereHas(
            'idInmueble', function ($query) use ($user) {
                switch ($user->role_id) {
                case 5:
                return $query->whereHas(
                'idProveedorReferente', function ($q) use ($user) {
                            $q->where('id_persona', $user->id_persona);
                }
                );
                break;
                case 4:
                    return $query->whereHas(
                        'idPropietarioReferente', function ($q) use ($user) {
                            return $q->where('id_persona', $user->id_persona);
                        }
		    );
                    break;
                case 3:
                    return $query->whereHas(
                        'idInquilinoReferente', function ($q) use ($user) {
                            return $q->where('id_persona', $user->id_persona);
                        }
                    );
                    break;
                case 2:
                    return $query->whereHas(
                        'idConserjeReferente', function ($q) use ($user) {
                            return $q->where('id_persona', $user->id_persona);
                        }
                    );
                break;
                case 1:
                    return $query->whereHas(
                        'idAdministradorReferente', function ($q) use ($user) {
                            return $q->where('id_persona', $user->id_persona);
                        }
                    );
                    break;
                } 
            }
        )
        ->orWhereHas(
            'unidades.idInmueble', function ($query) use ($user) {
                switch ($user->role_id) {
                case 5:
                    return $query->whereHas(
                        'idProveedorReferente', function ($q) use ($user) {
                            return $q->where('id_persona', $user->id_persona);
                        }
                    );
                    break;
                case 4:
                    return $query->whereHas(
                        'idPropietarioReferente', function ($q) use ($user) {
                            return $q->where('id_persona', $user->id_persona);
                        }
                    );
                break;
                case 3:
                    return $query->whereHas(
                        'idInquilinoReferente', function ($q) use ($user) {
                            return $q->where('id_persona', $user->id_persona);
                        }
                    );
                break;
                case 2:
                return $query->whereHas(
                    'idConserjeReferente', function ($q) use ($user) {
                         return $q->where('id_persona', $user->id_persona);
                    }
                );
                break;
                case 1:
                return $query->whereHas(
                    'idAdministradorReferente', function ($q) use ($user) {
                        return $q->where('id_persona', $user->id_persona);
                    }
                );
                break;
                }
            }
        )->get();
	    
        return $this->map($items);
    }

    /**
     * Find: Mis Inmuebles.
     *
     * @param User $user The User model.
     *
     * @return array
     */
    public function findMisInmuebles(User $user)
    {
        $user->idPersona->inmuebles->loadMissing('unidades');

        $items = $this->filter()->sort()->whereHas(
            'idInmueble', function ($query) use ($user) {
                $query->where('inmuebles.enum_estado', 'ACTIVO')->whereHas(
                    'personas', function ($q) use ($user) {
                        $q->where('personas.id', $user->id_persona)->where('personas.enum_estado', 'ACTIVO'); 
                    }
                ); 
        
                switch ($user->role_id) {
                // Rol Inquilino: No debe mostrar inmuebles que no tengan rentas activas.    
                case 3:
                    $query->whereHas(
                        'rentas', function ($q) use ($user) {
                            $q->where('id_inquilino', $user->id_persona);
                            $q->where('rentas.enum_estado', 'ACTIVO');
                        }
                    );
                }
            }
        )
        ->orWhereHas(
            'unidades', function ($query) use ($user) {
                $query->whereHas(
                    'idInmueble', function ($inmueble) use ($user) {
                        $inmueble->where('inmuebles.enum_estado', 'ACTIVO');
                        $inmueble->whereHas(
                            'personas', function ($persona) use ($user) {
                                $persona->where('personas.id', $user->id_persona);
                            }
                        );
        
                        switch ($user->role_id) {
                        // Rol Inquilino: No debe mostrar inmuebles que no tengan rentas activas.
                        case 3:
                            $inmueble->whereHas(
                                'rentas', function ($renta) use ($user) {
                                    $renta->where('id_inquilino', $user->id_persona);
                                    $renta->where('rentas.enum_estado', 'ACTIVO');
                                }
                            );
                        }
                    }
                );
            }
        )
        ->get();

        return $this->map($items);
    }

    /**
     * Find: Todos los inmuebles.
     *
     * @return array
     */
    public function findTodos()
    {
        $items = $this->filter()->sort()->with('unidades', 'idInmueble.caracteristicas', 'idInmueble.documentos')->get();

        return $this->map($items);
    }

    /**
     * Map items collection.
     *
     * @param Collection $items
     *
     * @return array
     */
    protected function map($items)
    {
        return $items->map(
            function ($item) {
                $inmueble = $item->idInmueble;

                return [
                'cant_pisos' => $item->cant_pisos,
                'cant_unidades' => $item->unidades->count(),
                'descripcion' => $inmueble->descripcion,
                'direccion' => $inmueble->direccion,
                'divisible_en_unidades' => $item->divisible_en_unidades,
                'formatos' => $item->idInmueble->formatos,
                'fotos' => $inmueble->unfeatured_photos,
                'id' => $item->id,
                'id_inmueble' => $inmueble,
                'id_usuario_creador' => $inmueble->id_usuario_creador,
                'inmueble_completo' => $item->modalidad_propiedad === 'UNICO_PROPIETARIO',
                'modalidad' => $item->modalidad_propiedad === 'UNICO_PROPIETARIO' ? 'Único Propietario' : 'En Condominio',
                'nombre' => $item->nombre,
                'nombre_y_apellidos_administrador_referente' => $inmueble->nombre_y_apellidos_administrador_referente,
                'nombre_y_apellidos_inquilino_referente' => $inmueble->nombre_y_apellidos_inquilino_referente,
                'nombre_y_apellidos_propietario_referente' => $inmueble->nombre_y_apellidos_propietario_referente,
                'persona_id_administrador_referente' => $inmueble->persona_id_administrador_referente,
                'persona_id_inquilino_referente' => $inmueble->persona_id_inquilino_referente,
                'solicitud_directa_inquilinos' => $inmueble->solicitud_directa_inquilinos ? 'Sí' : 'No',
                'tipo' => $inmueble->tipo,
                'unidades' => $item->unidades
                ];
            }
        );
    }
}
