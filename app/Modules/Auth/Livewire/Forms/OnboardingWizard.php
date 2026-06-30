<?php

declare(strict_types=1);

namespace App\Modules\Auth\Livewire\Forms;

use App\Modules\Blueprint\Actions\CreateBlueprint;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Actions\InviteUser;
use App\Modules\Organization\Exceptions\MaxOrganizationsReachedException;
use App\Modules\Organization\Models\Organization;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class OnboardingWizard extends Component
{
    public int $step = 1;

    // Step 2: Organization
    public string $orgName = '';

    // Step 3: Blueprint
    public string $bpTitle = '';

    public string $bpDescription = '';

    // Step 4: Invite
    public string $inviteEmail = '';

    public string $inviteRole = 'developer';

    // Internal state
    public ?int $createdOrgId = null;

    /**
     * Restore wizard step from database on mount.
     */
    public function mount(): void
    {
        $user = auth()->user();

        if ($user !== null && $user->onboarding_step !== null) {
            $this->step = (int) $user->onboarding_step;
        }
    }

    /**
     * Validation rules for all wizard properties.
     */
    protected function rules(): array
    {
        return [
            'orgName' => 'required|string|max:255',
            'bpTitle' => 'required|string|max:255',
            'bpDescription' => 'nullable|string|max:1000',
            'inviteEmail' => 'required|email',
        ];
    }

    /**
     * Real-time validation for individual properties.
     */
    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Navigate to a specific step and persist to database.
     *
     * Note: onboarding_step is intentionally NOT in $fillable.
     * We use direct property assignment to prevent mass-assignment
     * from request input, while still allowing internal persistence.
     */
    public function goToStep(int $step): void
    {
        $this->step = $step;

        $user = auth()->user();
        if ($user !== null) {
            $user->onboarding_step = $this->step;
            $user->save();
        }
    }

    /**
     * Submit organization creation (step 2, required).
     */
    public function submitOrg(CreateOrganization $createOrganization): void
    {
        $this->validate(['orgName' => 'required|string|max:255']);

        try {
            $slug = Str::slug($this->orgName);
            $user = auth()->user();

            $org = $createOrganization->execute(
                user: $user,
                name: $this->orgName,
                slug: $slug,
            );

            $this->createdOrgId = $org->id;
            $this->goToStep(3);
        } catch (MaxOrganizationsReachedException $e) {
            $this->addError('orgName', $e->getMessage());
        }
    }

    /**
     * Submit blueprint creation (step 3, skippable).
     */
    public function submitBlueprint(CreateBlueprint $createBlueprint): void
    {
        $this->validate(['bpTitle' => 'required|string|max:255']);

        $org = $this->getCreatedOrganization();
        if ($org === null) {
            $this->addError('bpTitle', __('Organization not found. Please go back and create one.'));

            return;
        }

        try {
            $slug = Str::slug($this->bpTitle);
            $createBlueprint->execute(
                organization: $org,
                title: $this->bpTitle,
                slug: $slug,
                description: $this->bpDescription !== '' ? $this->bpDescription : null,
            );

            $this->goToStep(4);
        } catch (\Exception $e) {
            $this->addError('bpTitle', $e->getMessage());
        }
    }

    /**
     * Submit member invitation (step 4, skippable).
     */
    public function submitInvite(InviteUser $inviteUser): void
    {
        $this->validate(['inviteEmail' => 'required|email']);

        $org = $this->getCreatedOrganization();
        if ($org === null) {
            $this->addError('inviteEmail', __('Organization not found.'));

            return;
        }

        try {
            $inviteUser->execute(
                organization: $org,
                email: $this->inviteEmail,
                role: $this->inviteRole,
            );

            $email = $this->inviteEmail;
            $this->inviteEmail = '';
            session()->flash('invite_success', __('onboarding.invite_success', ['email' => $email]));
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $errors) {
                foreach ($errors as $error) {
                    $this->addError($field, $error);
                }
            }
        }
    }

    /**
     * Skip the current step (only for steps 3 and 4).
     */
    public function skipStep(): void
    {
        $this->goToStep($this->step + 1);
    }

    /**
     * Mark onboarding as complete and redirect to dashboard.
     *
     * onboarding_step is assigned directly (not mass-assigned) because
     * it is intentionally excluded from $fillable.
     */
    public function complete(): void
    {
        $user = auth()->user();
        if ($user !== null) {
            $user->onboarding_completed_at = now();
            $user->onboarding_step = null;
            $user->save();
        }

        $this->redirect(route('dashboard'));
    }

    /**
     * Get the created organization from stored ID.
     */
    private function getCreatedOrganization(): ?Organization
    {
        if ($this->createdOrgId === null) {
            return null;
        }

        /** @var Organization|null $org */
        $org = Organization::find($this->createdOrgId);

        return $org;
    }

    public function render()
    {
        return view('auth::livewire.forms.onboarding-wizard');
    }
}
