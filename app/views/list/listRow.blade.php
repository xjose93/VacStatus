
<tr>
  <td class="vacstatus-list-avatar">
    <img src="{{{ $UserListProfile->avatar_thumb }}}">
  </td>
  <td class="vacstatus-list-user"><a href="{{{ URL::route('profile', Array('steam3Id'=> Steam\Steam::toBigId($UserListProfile->small_id) )) }}}" target="_blank">{{{ $UserListProfile->display_name }}}</a></td>
  @if($UserListProfile->vac > 0)
  <td class="vacstatus-list-status text-alert">
    <span class="fa fa-check"></span>&nbsp;&nbsp;03/19/2014
  </td>
  @else
  <td class="vacstatus-list-status text-success">
    <span class="fa fa-times"></span>
  </td>
  @endif
  <td class="vacstatus-list-tracker">{{{ $UserListProfile->get_num_tracking }}}</td>
  <td class="vacstatus-list-button">
    @if(Auth::check())
      @if(isset($personal) && $personal)
        <form action="{{{ URL::route('list_user_delete') }}}" method="POST">
          <input type="hidden" name="list_id" value="{{{ $UserListProfile->user_list_id }}}">
          <input type="hidden" name="profile_id" value="{{{ $UserListProfile->profile_id }}}">
          <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
          <button type="submit" class="button tiny alert-bg"><i class="fa fa-minus"></i></button>
        </form>
      @else
        <button class="button tiny" onClick="javascript:addUserList({{{ $UserListProfile->profile_id }}});"><i class="fa fa-plus"></i></button>
      @endif
    @endif
  </td>
</tr>
