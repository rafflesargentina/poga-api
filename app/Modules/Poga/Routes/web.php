<?php

use Raffles\Notifications\ResetPasswordNotification;

use Raffles\Modules\Poga\Models\{ Inmueble, Pagare, Renta, Unidad, User };
use Raffles\Modules\Poga\Mail\{ PagoConfirmadoAcreedor, PagoConfirmadoDeudor, PagoConfirmadoParaAdminPoga, PagoTransferidoAcreedor, PagoTransferidoParaAdminPoga, RegistroCompletadoParaAdminPoga, RentaCreadaParaAdminPoga, RentaCreadaParaInquilino, RentaCreadaParaPropietario, UsuarioCreadoParaAdminPoga };
use Raffles\Modules\Poga\Notifications\{ InmuebleCreado, InmuebleCreadoParaAdminPoga, InvitacionCreada, PagareCreadoParaAdminPoga, PagareCreadoPersonaAcreedora, PagareCreadoPersonaDeudora, PagareRentaPorVencerParaAdminPoga, PagareRentaPorVencerAcreedor, PagareRentaPorVencerDeudor, PagareRentaVencidoAcreedor, PagareRentaVencidoDeudor, PagareRentaVencidoParaAdminPoga, RegistroCompletado, RentaFinalizadaInquilinoReferente, RentaFinalizadaParaAdminPoga, RentaFinalizadaPropietarioReferente, RentaPorFinalizarInquilinoReferente, RentaPorFinalizarParaAdminPoga, RentaPorFinalizarPropietarioReferente, RentaRenovadaInquilinoReferente, RentaRenovadaPropietarioReferente, RentaRenovadaParaAdminPoga, UnidadCreada, UnidadCreadaParaAdminPoga, UsuarioRegistrado };
use Raffles\Modules\Poga\UseCases\TraerBoletaPago;

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your module. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
 */

if (App::environment(['local', 'staging'])) {
    Route::get('/mailing/inmueble-creado/{id}', function(Request $request) {
        $inmueble = Inmueble::find($request->id);

        $message = (new InmuebleCreado($inmueble))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/inmueble-creado-para-admin-poga/{id}', function(Request $request) {
        $inmueble = Inmueble::find($request->id);

        $message = (new InmuebleCreadoParaAdminPoga($inmueble, $inmueble->idUsuarioCreador))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/invitacion-creada/{id}', function(Request $request) {
        $user = User::find($request->id);

        $message = (new InvitacionCreada($user->idPersona, User::get()->random()->idPersona))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/pago-pendiente-para-acreedor/{id}', function(Request $request) {
	$pagare = Pagare::with('idInmueble', 'idUnidad')->where('id', $request->id)->firstOrFail();
	    
	$message = (new PagareCreadoPersonaAcreedora($pagare))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/pago-pendiente-para-deudor/{id}', function(Request $request) {
        $pagare = Pagare::find($request->id);

        $message = (new PagareCreadoPersonaDeudora($pagare))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/pago-pendiente-para-admin-poga/{id}', function(Request $request) {
        $pagare = Pagare::with('idInmueble', 'idUnidad')->where('enum_clasificacion_pagare', 'OTRO')->where('id', $request->id)->firstOrFail();

        $message = (new PagareCreadoParaAdminPoga($pagare))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/reestablecer-contrasena', function(Request $request) {
        $message = (new ResetPasswordNotification(str_random()))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/registro-completado/{id}', function(Request $request) {
        $user = User::find($request->id);

        $message = (new RegistroCompletado($user))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/registro-completado-para-admin-poga/{id}', function(Request $request) {
        $user = User::find($request->id);

        return new RegistroCompletadoParaAdminPoga($user);
    });

    Route::get('/mailing/renta-creada-para-inquilino/{id}', function(Request $request) {
	$job = new \Raffles\Modules\Poga\UseCases\TraerBoletaPago($request->id);

        $boleta = $job->handle();

        $user = User::get()->random();
        $renta = Renta::find($request->id);
        return new RentaCreadaParaInquilino($renta, $user, $boleta);
    });

    Route::get('/mailing/renta-creada-para-admin-poga/{id}', function(Request $request) {
        $job = new \Raffles\Modules\Poga\UseCases\TraerBoletaPago($request->id);

        $boleta = $job->handle();

        $user = User::get()->random();
        $renta = Renta::find($request->id);
        return new RentaCreadaParaAdminPoga($renta, $user, $boleta);
    });

    Route::get('/mailing/renta-creada-para-propietario/{id}', function(Request $request) {
        $job = new \Raffles\Modules\Poga\UseCases\TraerBoletaPago($request->id);

	$boleta = $job->handle();

        $user = User::get()->random();
        $renta = Renta::find($request->id);
        return new RentaCreadaParaPropietario($renta, $user, $boleta);
    });

    Route::get('/mailing/renta-finalizada-para-admin-poga/{id}', function(Request $request) {
        $renta = Renta::find($request->id);

        $message = (new RentaFinalizadaParaAdminPoga($renta))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/renta-finalizada-para-inquilino/{id}', function(Request $request) {
        $renta = Renta::find($request->id);

        $message = (new RentaFinalizadaInquilinoReferente($renta))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });    

    Route::get('/mailing/renta-finalizada-para-propietario/{id}', function(Request $request) {
        $renta = Renta::find($request->id);

        $message = (new RentaFinalizadaPropietarioReferente($renta))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/renta-renovada-para-admin-poga/{id}', function(Request $request) {
        $renta = Renta::find($request->id);

        $message = (new RentaRenovadaParaAdminPoga($renta))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });


    Route::get('/mailing/renta-renovada-para-inquilino/{id}', function(Request $request) {
        $renta = Renta::find($request->id);

        $message = (new RentaRenovadaInquilinoReferente($renta))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/renta-renovada-para-propietario/{id}', function(Request $request) {
        $renta = Renta::find($request->id);

        $message = (new RentaRenovadaPropietarioReferente($renta))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/solicitud-pago-para-acreedor/{id}', function(Request $request) {
        $job = new \Raffles\Modules\Poga\UseCases\TraerBoletaPago($request->id);

        $boleta = $job->handle();
        $pagare = Pagare::where('id', $request->id)->firstOrFail();

        $message = (new PagareCreadoPersonaAcreedora($pagare, $boleta))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/solicitud-pago-para-admin-poga/{id}', function(Request $request) {
        $job = new \Raffles\Modules\Poga\UseCases\TraerBoletaPago($request->id);

        $boleta = $job->handle();
        $pagare = Pagare::where('id', $request->id)->firstOrFail();

        $message = (new PagareCreadoParaAdminPoga($pagare, $boleta))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/solicitud-pago-para-deudor/{id}', function(Request $request) {
        $job = new \Raffles\Modules\Poga\UseCases\TraerBoletaPago($request->id);

        $boleta = $job->handle();
        $pagare = Pagare::where('id', $request->id)->firstOrFail();

        $message = (new PagareCreadoPersonaDeudora($pagare, $boleta))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/pagare-renta-por-vencer-para-admin-poga/{id}', function(Request $request) {
        $job = new \Raffles\Modules\Poga\UseCases\TraerBoletaPago($request->id);

        $boleta = $job->handle();
        $pagare = Pagare::where('id', $request->id)->firstOrFail();

        $message = (new PagareRentaPorVencerParaAdminPoga($pagare, $boleta))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/pagare-renta-por-vencer-para-deudor/{id}', function(Request $request) {
        $job = new \Raffles\Modules\Poga\UseCases\TraerBoletaPago($request->id);

	$boleta = $job->handle();

        $pagare = Pagare::where('id', $request->id)->firstOrFail();

        $message = (new PagareRentaPorVencerDeudor($pagare, $boleta))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/pagare-renta-por-vencer-para-acreedor/{id}', function(Request $request) {
        $job = new \Raffles\Modules\Poga\UseCases\TraerBoletaPago($request->id);

        $boleta = $job->handle();
        $pagare = Pagare::where('id', $request->id)->firstOrFail();

        $message = (new PagareRentaPorVencerAcreedor($pagare, $boleta))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/pagare-renta-vencido-para-admin-poga/{id}', function(Request $request) {
        $job = new \Raffles\Modules\Poga\UseCases\TraerBoletaPago($request->id);

        $boleta = $job->handle();
        $pagare = Pagare::where('id', $request->id)->firstOrFail();

        $message = (new PagareRentaVencidoParaAdminPoga($pagare, $boleta))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/pagare-renta-vencido-para-deudor/{id}', function(Request $request) {
        $job = new \Raffles\Modules\Poga\UseCases\TraerBoletaPago($request->id);

        $boleta = $job->handle();

        $pagare = Pagare::where('id', $request->id)->firstOrFail();

        $message = (new PagareRentaVencidoDeudor($pagare, $boleta))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/pagare-renta-vencido-para-acreedor/{id}', function(Request $request) {
        $job = new \Raffles\Modules\Poga\UseCases\TraerBoletaPago($request->id);

        $boleta = $job->handle();
        $pagare = Pagare::where('id', $request->id)->firstOrFail();

        $message = (new PagareRentaVencidoAcreedor($pagare, $boleta))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/pago-confirmado-para-acreedor/{id}', function(Request $request) {
        $job = new \Raffles\Modules\Poga\UseCases\TraerBoletaPago($request->id);
        $boleta = $job->handle();

        $pagare = Pagare::where('id', $request->id)->firstOrFail();
        $user = User::where('email', env('MAIL_ADMIN_ADDRESS'))->firstOrFail();

        return new PagoConfirmadoAcreedor($pagare, $user, $boleta);
    });

    Route::get('/mailing/pago-confirmado-para-admin-poga/{id}', function(Request $request) {
        $job = new \Raffles\Modules\Poga\UseCases\TraerBoletaPago($request->id);
	$boleta = $job->handle();

	$pagare = Pagare::where('id', $request->id)->firstOrFail();
        $user = User::where('email', env('MAIL_ADMIN_ADDRESS'))->firstOrFail();

        return new PagoConfirmadoParaAdminPoga($pagare, $user, $boleta);
    });

    Route::get('/mailing/pago-confirmado-para-deudor/{id}', function(Request $request) {
        $job = new \Raffles\Modules\Poga\UseCases\TraerBoletaPago($request->id);
        $boleta = $job->handle();

        $pagare = Pagare::where('id', $request->id)->firstOrFail();
        $user = User::where('email', env('MAIL_ADMIN_ADDRESS'))->firstOrFail();

        return new PagoConfirmadoDeudor($pagare, $user, $boleta);
    });

    Route::get('/mailing/pago-transferido-para-acreedor/{id}', function(Request $request) {
        $pagare = Pagare::with('idPagarePadre')->where('id_tabla', $request->id)->firstOrFail();
        $user = User::where('email', env('MAIL_ADMIN_ADDRESS'))->firstOrFail();

        return new PagoTransferidoAcreedor($pagare, $user);
    });

    Route::get('/mailing/pago-transferido-para-admin-poga/{id}', function(Request $request) {
        $pagare = Pagare::with('idPagarePadre')->where('id_tabla', $request->id)->firstOrFail();
        $user = User::where('email', env('MAIL_ADMIN_ADDRESS'))->firstOrFail();

        return new PagoTransferidoParaAdminPoga($pagare, $user);
    });

    Route::get('/mailing/propietario-registrado/{id}', function(Request $request) {
        $user = User::find($request->id);

        $message = (new UsuarioRegistrado($user))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/propietario-registrado-para-admin-poga/{id}', function(Request $request) {
        $user = User::find($request->id);

        return new UsuarioCreadoParaAdminPoga($user);
    });

    Route::get('/mailing/renta-por-finalizar-para-admin-poga/{id}', function(Request $request) {
        $renta = Renta::find($request->id);

        $message = (new RentaPorFinalizarParaAdminPoga($renta))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/renta-por-finalizar-para-inquilino/{id}', function(Request $request) {
        $renta = Renta::find($request->id);

        $message = (new RentaPorFinalizarInquilinoReferente($renta))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/renta-por-finalizar-para-propietario/{id}', function(Request $request) {
        $renta = Renta::find($request->id);

        $message = (new RentaPorFinalizarPropietarioReferente($renta))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/unidad-creada/{id}', function(Request $request) {
        $unidad = Unidad::find($request->id);

        $message = (new UnidadCreada($unidad))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });

    Route::get('/mailing/unidad-creada-para-admin-poga/{id}', function(Request $request) {
        $unidad = Unidad::find($request->id);

        $message = (new UnidadCreadaParaAdminPoga($unidad, $unidad->idInmueble->idUsuarioCreador))->toMail(User::where('email', env('MAIL_ADMIN_ADDRESS'))->first());
        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    });
}
