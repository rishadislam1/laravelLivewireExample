<?php

use App\Events\ChatMessage;
use Livewire\Component;

new class extends Component {
    public $textvalue = "";
    public $chatLog = array();

    public function getListeners(){
        return[
            "echo-private:chatchannel,ChatMessage"=>'notifyNewMessage'
        ];
    }

    public function notifyNewMessage($x)
    {
        array_push($this->chatLog,$x['chat']);
    }

    public function send(): void
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized');
        }
        if (trim(strip_tags($this->textvalue)) == "") {
            return;
        }

        array_push($this->chatLog, [
            'selfmessage' => true,
            'username' => auth()->user()->username,
            'textvalue' => strip_tags($this->textvalue),
            'avatar' => auth()->user()->avatar
        ]);
        broadcast(new ChatMessage([
            'selfmessage' => false,
            'username' => auth()->user()->username,
            'textvalue' => strip_tags($this->textvalue),
            'avatar' => auth()->user()->avatar
        ]))->toOthers();
        $this->textvalue = '';
    }
};
?>

<div x-data="{isOpen: false}">
    <span x-on:click="isOpen = true; document.querySelector('.chat-field').focus()"
          class="text-white mr-2 header-chat-icon" title="Chat" data-toggle="tooltip" data-placement="bottom"><i
            class="fas fa-comment"></i></span>

    @auth
        <div data-username="{{auth()->user()->username}}" data-avatar="{{auth()->user()->avatar}}" id="chat-wrapper"
             x-bind:class="isOpen?'chat--visible':''"
             class="chat-wrapper chat-wrapper--ready shadow border-top border-left border-right ">
            <div class="chat-title-bar">Chat <span x-on:click="isOpen=false" class="chat-title-bar-close"><i
                        class="fas fa-times-circle"></i></span></div>
            <div id="chat" class="chat-log">
                @if(count($chatLog)>0)
                    @foreach($chatLog as $chat)
                        @if($chat['selfmessage'] == true)
                            <div class="chat-self">
                                <div class="chat-message">
                                    <div class="chat-message-inner">
                                        {{$chat['textvalue']}}
                                    </div>
                                </div>
                                <img class="chat-avatar avatar-tiny" src="{{$chat['avatar']}}">
                            </div>
                        @else
                            <div class="chat-other">
                                <a href="/profile/{{$chat['username']}}"><img class="avatar-tiny" src="{{$chat['avatar']}}"></a>
                                <div class="chat-message"><div class="chat-message-inner">
                                        <a href="/profile/${data.username}"><strong>${{$chat['username']}}:</strong></a>
                                        {{$chat['textvalue']}}
                                    </div></div>
                            </div>
                        @endif

                    @endforeach
                @endif
            </div>

            <form wire:submit="send" id="chatForm" class="chat-form border-top">
                <input wire:model="textvalue" type="text" class="chat-field" id="chatField"
                       placeholder="Type a message…" autocomplete="off">
            </form>
        </div>
    @endauth
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('morph.added', (element) => {
            if (element.el.classList.contains("chat-self") || element.el.classList.contains("chat-other")) {
                const chat = document.querySelector("#chat")
                chat.scrollTop = chat.scrollHeight;
            }
        })
    })
</script>
