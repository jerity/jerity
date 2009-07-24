<?php
/**
 * @package  JerityCore
 * @author  Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * Notification class for standard formatting.
 *
 * @todo  Remove?  Used with Redirector.  Perhaps more suitable for UI package.
 *
 * @package  JerityCore
 * @author  Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */
class Notification implements Renderable {

  /**
   * Notification Types
   */
  const PLAIN       = 'msg_plain';
  const INFORMATION = 'msg_info';
  const WARNING     = 'msg_warn';
  const ERROR       = 'msg_error';

  /**
   * The message to be output.
   *
   * @var  string
   */
  protected $message = null;

  /**
   * The type of notification to render.
   * This is used as the CSS class.
   *
   * @var  string
   */
  protected $type = self::PLAIN;

  /**
   * Creates a new notification.
   *
   * @param  string  $message
   * @param  string  $type
   *
   * @throws  InvalidArgumentException
   */
  public function __construct($message = null, $type = self::PLAIN) {
    $this->setMessage($message);
    $this->setType($type);
  }

  /**
   * Render the item using the current global rendering context, and return it
   * as a string.
   *
   * @return string
   */
  public function render() {
    if (empty($this->message)) return '';

    # Get the current render context.
    $ctx = RenderContext::getGlobalContext();

    # Render the message appropriately
    switch ($ctx->getLanguage()) {
      case RenderContext::LANG_HTML:
      case RenderContext::LANG_XHTML:
        $output  = '<div class="'.$this->type.'">';
        $output .= $this->message;
        $output .= '</div>';
        break;
      case RenderContext::LANG_XML:
        $output  = '<notification type="'.$this->type.'">';
        $output .= $this->message;
        $output .= '</notification>';
        break;
      default:
        # TODO: Throw exception as we don't know how to render?
        $output = '';
    }
    return $output;
  }

  /**
   * Gets the message to be displayed.
   *
   * @return  string
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * Sets the message to be displayed.
   *
   * Note: No checking is performed on this message so that markup may be
   *       used.  Please ensure that you escape user input appropriately.
   *
   * @param  string  $message
   */
  public function setMessage($message) {
    $this->message = $message;
  }

  /**
   * Appends additional content to the message to be displayed.
   *
   * Note: No checking is performed on this message so that markup may be
   *       used.  Please ensure that you escape user input appropriately.
   *
   * @param  string  $message
   */
  public function appendMessage($message) {
    $this->message .= $message;
  }

  /**
   * Gets the type of the notification.  This type string is used as the CSS
   * class name.
   *
   * @return  string
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Sets the type of the notification.  This type string is used as the CSS
   * class name.
   *
   * @param  string  $type
   *
   * @throws  InvalidArgumentException
   */
  public function setType($type) {
    switch ($type) {
      case self::PLAIN:
      case self::INFORMATION:
      case self::WARNING:
      case self::ERROR:
        break;
      default:
        # TODO: Allow custom types?  Restrict to valid characters for CSS class.
        throw new InvalidArgumentException('Unrecognised notification type: '.$type);
    }

    $this->type = $type;
  }

}
