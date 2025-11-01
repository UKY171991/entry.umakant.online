@extends('adminlte::page')

@section('title', 'Email Transaction Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Email Transaction Details</h1>
        <div>
            <a href="{{ route('email-transactions.edit', $emailTransaction) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('email-transactions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Transaction Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Transaction Date:</strong>
                           