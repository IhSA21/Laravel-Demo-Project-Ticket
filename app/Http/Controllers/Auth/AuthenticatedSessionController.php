<?php

namespace App\Http\Controllers\Auth;

require_once __DIR__.'./../../../../vendor/autoload.php';

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\rabbitmqpublisherqos;
use App\Services\AMQPQOS;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use App\Services\AMQPConsume;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $publisher = new AMQPQOS('172.24.2.4', 5672, 'rabbitmq', 'rabbitmq', '/');

        $consumer = new AMQPStreamConnection('172.24.2.4', 5672, 'rabbitmq', 'rabbitmq', '/');

        $request->authenticate();

        $request->session()->regenerate();

        if ($request->user()->hasRole(['admin', 'agent', 'user'])) {
            $publisher->setAckHandler(function (AMQPMessage $message) {
                echo 'Message acked with content ' . $message->body . PHP_EOL;
            });
            $publisher->setNackHandler(function (AMQPMessage $message) {
                echo 'Message nacked with content ' . $message->body . PHP_EOL;
            });
            $publisher->confirmSelect();
            $publisher->queueDeclare('Test-2', false, true, false, false, false);
            $publisher->exchangeDeclare('Test-2', AMQPExchangeType::FANOUT, false, false, true);
            $publisher->queueBind('Test-2', 'Test-2');
            $data = [
                'Event' => 'New Login Requested',
                'User' => auth()->user()->name,
                'Role' => auth()->user()->getRoleNames(),
            ];
            $datajson = json_encode($data);
            $i = 1;
            $msg = new AMQPMessage($i, array('content_type' => 'application/json', 'message' => $datajson));
            $publisher->basicPublish($msg, $datajson, 'Test-2', 1);
            $publisher->setConsumerPrefetchCount(0, 1, false);
            $publisher->waitForPendingAcks();
            while ($i <= 1) {
                $msg = new AMQPMessage($i++, array('content_type' => 'application/json', 'message' => $datajson));
                $publisher->basicPublish($msg, $datajson, 'Test-2', 1);
            }
            $publisher->waitForPendingAcks();

            $publisher->close();
            $consumer = new AMQPConsume('Test-2', 'Test-2', 'consumer');
            $consumer->processMessage($publisher);
            $consumer->consume();
            $consumer->wait();
            $consumer->__destruct();
        
            return redirect('/dashboard');
        }

        return to_route('tickets.index');
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
