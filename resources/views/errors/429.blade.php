@extends('errors.layout')

@section('title', 'Trop de requêtes')
@section('code', '429')
@section('message', 'Trop de requêtes')
@section('description', 'Vous avez effectué trop de requêtes en peu de temps. Veuillez patienter quelques instants avant de réessayer.')