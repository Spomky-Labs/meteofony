import { startStimulusApp } from '@symfony/stimulus-bundle';
import { AuthenticationController, RegistrationController } from '@web-auth/webauthn-stimulus';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);

// The WebAuthn controllers come from npm, so they are registered here and not in controllers.json, which
// only takes Symfony UX PHP packages. The identifiers are those stimulus_controller() renders for
// '@web-auth/webauthn-stimulus/authentication' and '@web-auth/webauthn-stimulus/registration'. No
// template uses them yet: WebAuthn is prepared, not enabled (config/packages/webauthn.yaml).
app.register('web-auth--webauthn-stimulus--authentication', AuthenticationController);
app.register('web-auth--webauthn-stimulus--registration', RegistrationController);
