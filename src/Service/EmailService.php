<?php
namespace PrimeSoftware\Service;

use Twig\Environment;

abstract class EmailService {

	/**
	 * @var \Swift_Mailer
	 */
	private $mailer;

	/**
	 * @var Environment
	 */
	private $twig;

	/**
	 * Email address to send emails from
	 * @var string
	 */
	protected $fromEmail = 'test@test.com';

	/**
	 * Name of the person to send emails from
	 * @var string
	 */
	protected $fromName = 'Test';

	/**
	 * Holds the twig file used when generating emails
	 * @var string
	 */
	protected $baseTemplate = 'email.twig';

	public function __construct( \Swift_Mailer $mailer, Environment $twig ) {
		$this->mailer = $mailer;
		$this->twig = $twig;
	}

	/**
	 * Sets the from details for the email
	 * @param $emailAddress
	 * @param null $name
	 * @return $this
	 */
	public function setFromDetails( $emailAddress, $name = null ) {
		$this->fromEmail = $emailAddress;
		$this->fromName = $name;

		return $this;
	}

	/**
	 * Sets the template to use for generating the emails
	 * @param $template
	 */
	public function setTwigTemplate( $template ) {
		$this->baseTemplate = $template;

		return $this;
	}

	/**
	 * @param $dest_email
	 * @param $dest_name
	 * @param $subject
	 * @param $body
	 * @param array $attachements
	 * @param array $params
	 * @return mixed
	 */
	public function send_email( $dest_email, $dest_name, $subject, $body, $attachements = [], $params = [] ) {

		$params[ 'template' ] = $subject;
		// Generate the html for the subject
		$subject = $this->twig->render( $this->baseTemplate, $params );

		$params[ 'template' ] = $body;
		// Generate the html for the body
		$body = $this->twig->render( $this->baseTemplate, $params );

		// Build the message
		$message = ( new \Swift_Message( $subject ) )
			->setFrom( $this->fromEmail, $this->fromName )
			->setTo( $dest_email, $dest_name )
			->setBody(
				$body,
				'text/html'
			);

		foreach( $attachements as $attachement ) {
			// Create the attachment and call its setFilename() method
			$attachment_obj = \Swift_Attachment::fromPath( $attachement['path'], (isset($attachement[ 'type' ]) ? $attachement[ 'type' ] : null) )
				->setFilename( $attachement['name'] );

			$message->attach( $attachment_obj );
		}

		$result = $this->mailer->send( $message );

		return $result;
	}
}
