<?php

require __DIR__ . '/vendor/autoload.php';

use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return 'Error: Expecting a POST request';
}

// initialize the printer connection, printing directly to the raw usb connection
$connector = new FilePrintConnector("/dev/usb/lp0");
$printer = new Printer($connector);

// grab the full input coming in as JSON via a POST request
$data = json_decode(file_get_contents('php://input'), true);

// print out different sections of the receipt
// breaking this up into functions for clarity and readability
printHeader($printer, $data['issue']['user']['login'], $data['repository']['name']);
printTitle($printer, $data['issue']['title']);
printBody($printer, $data['issue']['body']);
printFooter($printer, $data['issue']['created_at']);

// all good!
return 0;

// functions for printing individual parts of the receipt
function printHeader($printer, $user, $repo)
{
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setTextSize(2, 2);
    $printer->setUnderline(true);
    $printer->setEmphasis(true);
    $printer->text("New Issue\n");
    $printer->feed(2);

    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->setTextSize(1, 1);
    $printer->setUnderline(false);
    $printer->setEmphasis(false);
    $printer->text("Repo: aschmelyun/" . $repo . "\n");
    $printer->text("User: @" . $user);

    $printer->feed(2);
}

function printTitle($printer, $title)
{
    $printer->setEmphasis(true);
    $printer->text($title);
    $printer->setEmphasis(false);
    $printer->feed(2);
}

function printBody($printer, $body)
{
    $printer->text(wordwrap($body, 42));
    $printer->feed(2);
}

function printFooter($printer, $timestamp)
{
    $printer->text($timestamp);
    $printer->feed(2);
    $printer->cut();
}
