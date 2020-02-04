<?php

namespace Raffles\Modules\Poga\Repositories;

use Raffles\Modules\Poga\Models\Renta;

use Caffeinated\Repository\Repositories\EloquentRepository;
use Illuminate\Http\Request;

class RentaRepository extends EloquentRepository
{
    /**
     * @var Model
     */
    public $model = Renta::class;

    /**
     * @var array
     */
    public $tag = ['Renta'];

    /**
     * Rentas Activas con Multa.
     *
     * @return Collection
     */
    public function activasConMulta()
    {
        return $this->findWhere(['multa' => 1, 'enum_estado' => 'ACTIVO']);
    }

    /**
     * findRentas.
     *
     * @return array
     */
    public function todos()
    {
        return $this->with('idUnidad')->filter()->sort()->get();
    }

    /**
     * Mis Rentas.
     *
     * @param Request $request
     *
     * @return array
     */
    public function misRentas(Request $request)
    {
        $user = $request->user();

	// Tira error con filters y sorts.
        return $this->with('idInmueble', 'idUnidad.idFormatoInmueble')->whereHas(
                'idInmueble', function ($query) use ($user) {
			
		    switch ($user->role_id) {
		    case 5:
                        return $query->whereHas(
                            'idProveedorReferente', function ($q) use($user) {
                                $q->where('id_persona', $user->id_persona);
                            }
                        );
                    break;
		    case 4:
			return $query->whereHas(
                            'idPropietarioReferente', function ($q) use($user) {
				return $q->where('id_persona', $user->id_persona);
                            }
                        );
                    break;
                    case 3:
                        return $query->whereHas(
                            'idInquilinoReferente', function ($q) use($user) {
                                return $q->where('id_persona', $user->id_persona);
                            }
                        );
                    break;
                    case 2:
                        return $query->whereHas(
                            'idConserjeReferente', function ($q) use($user) {
                                return $q->where('id_persona', $user->id_persona);
                            }
                        );
                    break;  
                    case 1:
                        return $query->whereHas(
                            'idAdministradorReferente', function ($q) use($user) {
                                return $q->where('id_persona', $user->id_persona);
                            }
                        );
                    break;
		    }
		})    
        ->get();
    }
}
