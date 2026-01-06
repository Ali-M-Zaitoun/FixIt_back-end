<?php

namespace App\DAO;

use App\Models\Reply;

class ReplyDAO
{
    public function addReply($complaint_id, $model, $content)
    {
        return $model->replies()->create([
            'complaint_id' => $complaint_id,
            'content'      => $content
        ]);
    }

    public function read($complaint)
    {
        return $complaint->replies;
    }

    public function readOne($id)
    {
        return Reply::where('id', $id)->first();
    }

    public function update($reply, $content)
    {
        return $reply->update([
            'content' => $content ?? $reply['content']
        ]);
    }

    public function delete($reply)
    {
        return $reply->delete();
    }
}
