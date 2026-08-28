@if ($errors->any())
  <div class="msg msg-err">
    {{ $errors->count() }} champ(s) à corriger avant d'enregistrer.
  </div>
@endif
