<x-mail::message>
# እንኳን በደህና መጡ!

ውድ {{ $member->full_name }}፣

የ **{{ $idirName }}** እድር ምዝገባዎ ጸድቋል። አሁን አገልግሎት መጀመር ይችላሉ።

<x-mail::button :url="url('/member')" color="primary">
ወደ ዋና ገጽ ይሂዱ
</x-mail::button>

እናመሰግናለን!<br>
{{ $idirName }}
</x-mail::message>
