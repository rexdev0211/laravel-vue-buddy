<link rel="stylesheet" href="{{ asset('backend/plugins/datepicker/bootstrap-datepicker.min.css') }}">
<script src="{{ asset('backend/plugins/datepicker/bootstrap-datepicker.min.js') }}"></script>
<script type="text/javascript">
    var userData = {!! json_encode($user) !!};

    Vue.component('buddy-link', {
        name: 'buddy-link',
        template: '#buddy-link-template',
        data() {
            return {
                buddy_link: userData.link,
                initial_buddy_link: userData.link,
                user: userData,
                mode: 'view'
            }
        },
        methods: {
            enterEditMode(){
                this.initial_buddy_link = this.buddy_link
                this.mode = 'edit'
                this.$nextTick(function(){
                    this.$refs.input.focus()
                })
            },
            exitEditMode(){
                console.log('[Buddy Link] exitEditMode')
                this.buddy_link = this.initial_buddy_link
                this.mode = 'view'
            },
            save() {
                console.log('[Buddy Link] save')
                let self = this;
                makeAjaxRequest(`/admin/users/buddyLink/${this.user.id}`, {buddyLink: this.buddy_link}, 'POST', true)
                    .then(data => {
                        console.log('[Buddy Link Response]', data);
                        self.mode = 'view';
                        self.initial_buddy_link = self.buddy_link
                    })
                    .catch(data => {
                        console.log('[Buddy Link Error]', data);
                    })
            }
        }
    });

    var app = new Vue({
        el: '#app',
        data: {
            datepickerInit: false,
            user: userData,
            upgrade: false,
            updatePassword: false,
        },
        methods: {
            showUpgradeUser() {
                this.upgrade = true
                this.hideUpdatePassword();

                if (!this.datepickerInit) {
                    this.datepickerInit = true
                    setTimeout(function() {
                        $('.datepicker').datepicker({
                            format: 'dd.mm.yyyy',
                        })
                    }, 200)
                }
            },
            hideUpgradeUser() {
                this.upgrade = false
            },
            showUpdateUserPassword() {
                this.updatePassword = true;
                this.hideUpgradeUser();
            },
            hideUpdatePassword() {
                this.updatePassword = false;
            },
            updateUserPassword() {
                let password = {
                    'password': $('#newUserPassword').val()
                };

                makeAjaxRequest(`/admin/users/updatePassword/${this.user.id}`, password, 'PATCH', true)
                    .then(data => {
                        this.goToUsersList();
                    })
            },
            downgradeUser() {
                makeAjaxRequest(`/admin/users/downgrade/${this.user.id}`)
                    .then(data => {
                        this.goToUsersList()
                    })
            },
            upgradeUser() {
                let proExpiresAt = $('#proExpiresAt').val()
                makeAjaxRequest(`/admin/users/upgrade/${this.user.id}?date=${proExpiresAt}`)
                    .then(data => {
                        this.goToUsersList()
                    })
            },
            whitelistUser() {
                makeAjaxRequest(`/admin/users/whitelist/${this.user.id}`)
                    .then(data => {
                        this.goToUsersList()
                    })
            },
            blacklistUser() {
                makeAjaxRequest(`/admin/users/blacklist/${this.user.id}`)
                    .then(data => {
                        this.goToUsersList()
                    })
            },
            ghostUser() {
                makeAjaxRequest(`/admin/users/ghost/${this.user.id}`)
                    .then(data => {
                        this.goToUsersList()
                    })
            },
            suspendUser() {
//                this.user.status = 'suspended';

                makeAjaxRequest(`/admin/users/suspend/${this.user.id}`)
                    .then(data => {
                        this.goToUsersList()
                    })
            },
            activateUser() {
//                this.user.status = 'active';

                makeAjaxRequest(`/admin/users/activate/${this.user.id}`)
                    .then(data => {
                        this.goToUsersList()
                    })
            },
            softDeleteUser() {
//                this.user.deleted_at = new Date();

                confirmDeletePromise(this.user.name, 'This action will mark user as deleted')
                    .then(function () {
                        makeAjaxRequest(`/admin/users/softDelete/${this.user.id}`, {}, 'delete')
                            .then(data => {
                                this.goToUsersList()
                            })
                    }.bind(this))
                    .catch(swal.noop);
            },
            hardDeleteUser() {
                confirmDeletePromise(this.user.name, 'This action will remove all user info from database and hard drive. It is irreversible.')
                    .then(function () {
                        makeAjaxRequest(`/admin/users/hardDelete/${this.user.id}`, {}, 'delete')
                            .then(data => {
                                this.goToUsersList()
                            })
                    }.bind(this))
                    .catch(swal.noop);
            },
            restoreUser() {
//                this.user.deleted_at = null;

                makeAjaxRequest(`/admin/users/restore/${this.user.id}`)
                    .then(data => {
                        this.goToUsersList()
                    })
            },
            goToUsersList() {
                window.location.reload();
                // window.location.href = '{{ route('admin.users') }}';
            }
        }
    })
</script>

<style type="text/css">
    .link-trigger {
        text-decoration: none;
        border-bottom: 1px dashed;
    }
</style>

@verbatim
<script type="text/x-template" id="buddy-link-template">
    <div>
        <a
            v-if="mode === 'view'"
            class="link-trigger"
            href="#"
            @click.prevent="enterEditMode"
        >{{ buddy_link }}</a>
        <input
            v-if="mode === 'edit'"
            ref="input"
            type="text"
            v-model="buddy_link"
            @blur="exitEditMode"
            @keyup.enter="save"
        />
    </div>
</script>
@endverbatim

<section class="content" id="app">
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">Profile photo</h3>
                        </div>
                        <div :class="{'box-body': true, 'text-center': true, 'bg-red': user.deleted_at != null, 'bg-gray': user.status == 'suspended'}">
                            <a data-lity href="{{ $user->getRawPhotoUrlByKey('clear.orig') }}">
                                <img src="{{ $user->getRawPhotoUrlByKey('clear.small') }}" alt="">
                            </a>
                            <a data-lity href="{{ $user->getRawPhotoUrlByKey('adult.orig') }}">
                                <img src="{{ $user->getRawPhotoUrlByKey('adult.small') }}" alt="">
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">Public gallery: {{ count($publicPhotos) }} photos</h3>
                        </div>
                        <div class="box-body">
                            @foreach($publicPhotos as $photo)
                                <a data-lity href="{{ $photo->getUrl('orig', true) }}">
                                    <img src="{{ $photo->getUrl('65x65', true) }}" style="margin: 0 5px 5px 0">
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">Private gallery: {{ count($privatePhotos) }} photos</h3>
                        </div>
                        <div class="box-body">
                            @foreach($privatePhotos as $photo)
                                <a data-lity href="{{ $photo->getUrl('orig', true) }}">
                                    <img src="{{ $photo->getUrl('65x65', true) }}" style="margin: 0 5px 5px 0">
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-md-12" v-if="upgrade">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">Upgrade user status</h3>
                        </div>
                        <div class="box-body">
                            <div class="form-group">
                                <label for="proExpiresAt">Select PRO expiration date</label>
                                <input type="text" class="form-control datepicker" id="proExpiresAt" value="{{ !$user->isStaff() && $user->isPro() ? $user->pro_expires_at->format('d.m.Y') : '' }}">
                            </div>
                            <button type="button" class="btn btn-sm btn-success" v-on:click="upgradeUser">Save</button>
                            <button type="button" class="btn btn-sm btn-danger" v-on:click="downgradeUser">Downgrade</button>
                            <button type="button" class="btn btn-sm btn-secondary" v-on:click="hideUpgradeUser">Cancel</button>
                        </div>
                    </div>
                </div>

                <div class="col-md-12" v-if="updatePassword">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">Update user password</h3>
                        </div>
                        <div class="box-body">
                            <div class="form-group">
                                <label for="newUserPassword">Enter new password</label>
                                <input type="password" class="form-control datepicker" id="newUserPassword">
                            </div>
                            <button type="button" class="btn btn-sm btn-success" v-on:click="updateUserPassword">Save</button>
                            <button type="button" class="btn btn-sm btn-secondary" v-on:click="hideUpdatePassword">Cancel</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">Actions</h3>
                        </div>
                        <div class="box-body">
                            <a href="{{ route('admin.loginAsUser', $user->id) }}" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-user"></i> Login</a>
                            @if (!$user->isStaff())
                            <button type="button" class="btn btn-sm btn-success" v-on:click="showUpgradeUser">Upgrade</button>
                            @endif
                            <button type="button" class="btn btn-sm btn-warning" v-on:click="showUpdateUserPassword">Update password</button>
                            {{--<a v-if="user.status != 'suspended' && user.deleted_at == null" href="{{ route('admin.loginAsUser', $user->id) }}" target="_blank" class="btn btn-sm btn-warning"><i class="fa fa-user"></i> Login</a>--}}
                            {{--<a v-else disabled href="javascript:void(0)" class="btn btn-sm btn-warning"><i class="fa fa-user"></i> Login</a>--}}

                            <a v-if="!user.trusted_message_sender" href="javascript:void(0)" v-on:click="whitelistUser" class="btn btn-sm btn-success"><i class="fa fa-check"></i> Whitelist</a>
                            <a v-else href="javascript:void(0)" v-on:click="blacklistUser" class="btn btn-sm btn-warning"><i class="fa fa-ban"></i> Blacklist</a>

                            <a v-if="user.status != 'active'" href="javascript:void(0)" v-on:click="activateUser" class="btn btn-sm btn-success"><i class="fa fa-check-circle"></i> Activate</a>
                            <a v-if="user.status != 'suspended'" href="javascript:void(0)" v-on:click="suspendUser" class="btn btn-sm btn-warning"><i class="fa fa-ban"></i> Suspend</a>
                            <a v-if="user.status != 'ghosted'" href="javascript:void(0)" v-on:click="ghostUser" class="btn btn-sm btn-grey"><i class="fa fa-ban"></i> Ghost</a>

                            <a v-if="user.deleted_at == null" href="javascript:void(0)" v-on:click="softDeleteUser" class="btn btn-sm btn-danger"><i class="fa fa-trash-o"></i> Soft delete</a>
                            <a v-else href="javascript:void(0)" v-on:click="restoreUser" class="btn btn-sm btn-success"><i class="fa fa-trash"></i> Restore</a>

                            <a href="javascript:void(0)" v-on:click="hardDeleteUser" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i> Hard delete</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">User information</h3>
                        </div>

                        <div class="box-body">
                            <div class="form-group {{ !$user->isStaff() && $user->isPro() ? 'col-md-6' : 'col-md-12' }}">
                                <label>Group </label>
                                <div>{{ $user->group_full }}</div>
                            </div>

                            @if (!$user->isStaff() && $user->isPro())
                            <div class="form-group col-md-6">
                                <label>PRO </label>
                                <div>{{ ucfirst($user->pro_type) }} ({{ $user->proExpiresAt() }})</div>
                            </div>
                            @endif

                            @if ($user->name)
                                <div class="form-group col-md-6">
                                    <label>Username </label>
                                    <div>{{ $user->name }}</div>
                                </div>
                            @endif

                            @if ($user->link)
                                <div class="form-group col-md-6">
                                    <label>Buddy Link</label>
                                    <div><buddy-link buddy_link="{{ $user->link }}"></buddy-link></div>
                                </div>
                            @endif

                            @if ($user->id)
                                <div class="form-group col-md-6">
                                    <label>ID </label>
                                    <div>{{ $user->id }}</div>
                                </div>
                            @endif

                            @if ($user->email || $user->email_orig)
                                <div class="form-group col-md-6">
                                    <label>Email </label>
                                    <div>{{ $user->email ?: $user->email_orig }}</div>
                                </div>
                            @endif

                            @if ($user->email_validation)
                                <div class="form-group col-md-6">
                                    <label>Email validation</label>
                                    <div>{{ $user->email_validation }}</div>
                                </div>
                            @endif

                            @if ($user->dob)
                                <div class="form-group col-md-6">
                                    <label>DOB </label>
                                    <div>{{ Helper::formatDate($user->dob) }}</div>
                                </div>
                            @endif

                            @if ($user->address)
                                <div class="form-group col-md-12">
                                    <label>Address</label>
                                    <div>{{ $user->address }}</div>
                                </div>
                            @endif

                            @if ($user->about)
                                <div class="form-group col-md-12">
                                    <label>About</label>
                                    <div>{{ $user->about }}</div>
                                </div>
                            @endif

                            @if ($user->height)
                                <div class="form-group col-md-6">
                                    <label>Height</label>
                                    <div>{{ $user->height }}cm</div>
                                </div>
                            @endif

                            @if ($user->weight)
                                <div class="form-group col-md-6">
                                    <label>Weight</label>
                                    <div>{{ $user->weight }}kg</div>
                                </div>
                            @endif

                            @if ($user->body)
                                <div class="form-group col-md-6">
                                    <label>Body</label>
                                    <div>{{ $user->body }}</div>
                                </div>
                            @endif

                            @if ($user->penis)
                                <div class="form-group col-md-6">
                                    <label>Penis</label>
                                    <div>{{ $user->penis }}</div>
                                </div>
                            @endif

                            @if ($user->position)
                                <div class="form-group col-md-6">
                                    <label>Position</label>
                                    <div>{{ $user->position }}</div>
                                </div>
                            @endif

                            @if ($user->hiv)
                                <div class="form-group col-md-6">
                                    <label>HIV</label>
                                    <div>{{ $user->hiv }}</div>
                                </div>
                            @endif

                            @if ($user->drugs)
                                <div class="form-group col-md-6">
                                    <label>Drugs</label>
                                    <div>{{ $user->drugs }}</div>
                                </div>
                            @endif

                            @if ($user->show_age)
                                <div class="form-group col-md-6">
                                    <label>Show age</label>
                                    <div>{{ $user->show_age }}</div>
                                </div>
                            @endif

                            @if ($user->unit_system)
                                <div class="form-group col-md-6">
                                    <label>Unit system</label>
                                    <div>{{ $user->unit_system }}</div>
                                </div>
                            @endif

                            @if ($user->language)
                                <div class="form-group col-md-6">
                                    <label>Language</label>
                                    <div>{{ $user->language}}</div>
                                </div>
                            @endif

                            @if ($user->subscribed)
                                <div class="form-group col-md-6">
                                    <label>Subscribed</label>
                                    <div>{{ $user->subscribed}}</div>
                                </div>
                            @endif

                            @if (count($user->tags))
                                <div class="form-group col-md-12">
                                    <label>Tags</label>
                                    <div>
                                        @foreach($user->tags as $tag)
                                            <span class="text-nowrap bg-gray" style="margin: 0 5px 5px 0">#{{ $tag->name }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        <!-- /.box-body -->
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">Fingerprint</h3>
                        </div>

                        <div class="box-body">
                            @if ($user->ip)
                            <div class="form-group col-md-4">
                                <label>IP</label>
                                <div><a href="#" onclick="confirmBlockIp('{{ $user->ip }}')">{{ $user->ip }}</a></div>
                            </div>
                            @endif
                            @if ($user->honeypot)
                            <div class="form-group col-md-4">
                                <label>Honeypot</label>
                                <div>{{ $user->honeypot }}</div>
                            </div>
                            @endif
                            @if ($user->map_type != 'none')
                            <div class="form-group col-md-4">
                                <label>Map Type</label>
                                <div>{{ ucfirst($user->map_type) }}</div>
                            </div>
                            @endif
                            @if ($user->fingerprint)
                            <div class="form-group col-md-12">
                                <label>Fingerprint</label>
                                <div>{{ $user->fingerprint }}</div>
                            </div>
                            @endif
                        </div>
                        <!-- /.box-body -->
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">Statistics</h3>
                        </div>

                        <div class="box-body">
                            <div class="form-group col-md-6">
                                <label>Last active</label>
                                <div>{{ Helper::formatDate($user->last_active) . ' ' . $user->activityStatus }}</div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Join date</label>
                                <div>{{ Helper::formatDate($user->created_at) }}</div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Status</label>
                                <div :class="{'text-green': user.computed_status == 'Active', 'text-red': user.computed_status != 'Active'}">
                                    @{{ user.computed_status }}
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>His profile viewed</label>
                                <div>{{ $user->his_profile_viewed }} times</div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Viewed other profiles</label>
                                <div>{{ $user->viewed_other_profiles }} times</div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Time spent online</label>
                                <div>{{ intval($user->time_spent_online/60) }} minutes</div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Total favorites</label>
                                <div>{{ $favoritesCount }} users</div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Messaged people (week/month/year)</label>
                                <div>{{ $msgWeek }} ppl / {{ $msgMonth }} ppl / {{ $msgYear }} ppl</div>
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                </div>

                @if ($antispam)
                <div class="col-md-12">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">Testing Anti-spam</h3>
                        </div>

                        <div class="box-body">
                            <div class="form-group col-md-6">
                                <label>Should be suspended:</label>
                                <div>{{ $antispam['suspended'] ? 'Yes' : 'No' }}</div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Should be ghosted:</label>
                                <div>{{ $antispam['ghosted'] ? 'Yes' : 'No' }}</div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>IP Blocked:</label>
                                <div>{{ $antispam['ip_blocked'] ? 'Yes' : 'No' }}</div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>IP Ghosted:</label>
                                <div>{{ $antispam['ghosted_ips'] ? 'Yes' : 'No' }}</div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Message Limit:</label>
                                <div>{{ $antispam['message_limit'] ? 'Yes' : 'No' }}</div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>10 or more similar messages:</label>
                                <div>{{ $antispam['10_or_more_similar'] ? 'Yes' : 'No' }}</div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Email bounced:</label>
                                <div>{{ $antispam['email_bounced'] ? 'Yes' : 'No' }}</div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Multiple registrations:</label>
                                <div>{{ $antispam['multiple_reg'] ? 'Yes' : 'No' }}</div>
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
