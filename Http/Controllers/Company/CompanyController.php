<?php

namespace Modules\Accounts\Http\Controllers\Company;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Transformers\Company\CompanyResource;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {

        if(auth('api')->user()->cannot('viewAny', Company::class)){
            abort(403);
        }

        return CompanyResource::collection (Company::all ());
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse|object
     */
    public function store(Request $request)
    {
        if(auth('api')->user()->cannot('create', Company::class)){
            abort(403);
        }
        // Todo::Returned status 200

        $company = Company::create($this->validateData());

        Log::notice('Successfully stored new company', ['user_id' => auth ()->user ()->user->id,
            'username' => auth ()->user ()->getHashedUsername(auth ()->user ()->username),
            'ip' => request ()->ip (),
            'company_id' => $company->id
        ]);

        return (new CompanyResource($company))
            ->response ()
            ->setStatusCode (Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse|object
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($company)
    {
        $company = Company::findOrFail($company);

        if(auth('api')->user()->cannot('view', $company)){
            abort(403);
        }

        return (new CompanyResource($company))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse|object
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, $company)
    {
        $company = Company::findOrFail($company);
        if(auth('api')->user()->cannot('update', $company)){
            abort(403);
        }
        $company->update($this->validateDataForUpdate());
        $company->fresh();

        Log::notice('Successfully updated the company', ['credential_id' => auth ('api')->user ()->id,
            'username' => auth ('api')->user ()->getHashedUsername(auth ('api')->user ()->username),
            'ip' => request ()->ip (),
            'company_id' => $company->id
        ]);

        return (new CompanyResource($company))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse|object
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($company)
    {
        $company = Company::findOrFail($company);
        if(auth('api')->user()->cannot('delete',Company::class)){
            abort(403);
        }
        $company->delete();

        Log::notice('Successfully deleted the company', ['credential_id' => auth ('api')->user ()->id,
            'username' => auth ('api')->user ()->getHashedUsername(auth ('api')->user ()->username),
            'ip' => request ()->ip (),
            'company_id' => $company->id
        ]);

        return (new CompanyResource($company))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }

    private function validateData()
    {
        return request ()->validate ([
            'name' => 'required|max:255',
            'description' => 'required|max:255',
            'company_website' => 'required|url|max:255'
        ]);
    }

    private function validateDataForUpdate()
    {
        return request ()->validate ([
            'name' => 'max:255',
            'description' => 'max:255',
            'company_website' => 'url|max:255'
        ]);
    }
}
