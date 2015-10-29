<?php
namespace PQD;
/**
 *
 * @author Willker Moraes Silva
 * @since 2012-06-25
 *
 */
interface PQDWorkflow{

	public function mountMail();

	public function getHtml();

	public function send();
}