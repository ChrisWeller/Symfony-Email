<?php
namespace PrimeSoftware\Service;

use Twig\Environment;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

abstract class EmailService {

	/**
	 * @var MailerInterface
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

	public function __construct( MailerInterface $mailer, Environment $twig ) {
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
		$message = ( new Email( ) )
			->from( $this->fromEmail, $this->fromName )
			->to( $dest_email, $dest_name )
			->subject( $subject )
			->html( $body );

		if ( $attachements !== null ) {
			foreach ($attachements as $attachement) {
				$message->attach(fopen($attachement['path'], 'r'));
			}
		}

		try {
			$this->mailer->send($message);

			return true;
		}
		catch( TransportExceptionInterface $e ) {
			echo $e->getMessage() . "\n";
			return false;
		}
	}
}
